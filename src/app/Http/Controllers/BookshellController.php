<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookshellController extends Controller
{
    // 色の候補(本のidから決めるので、毎回同じ色になる)
    private const COLORS = ['#7a4a28', '#3d5f7a', '#7a3f4a', '#4f7a4f', '#8a7a35', '#5f4a7a', '#a34a2c', '#2f6a6a'];

    public function index()
    {
        $books = Auth::user()->books()->orderBy('book_user.created_at')->get()
            ->map(function ($book) {
                $lastRead = $book->pivot->last_read_at;

                // 動きがあって、まだ開いていない(または開いた後に動きがあった)なら未読
                $unread = $book->last_activity_at
                    && (!$lastRead || $book->last_activity_at->gt(Carbon::parse($lastRead)));

                return [
                    'id'     => $book->id,
                    'title'  => $book->title,
                    'color'  => self::COLORS[$book->id % count(self::COLORS)],
                    'height' => 170 + ($book->id * 17) % 70,
                    'unread' => (bool) $unread,
                ];
            })
            ->all();

        return view('bookshells.index', compact('books'));
    }

    // 本を作成(作った人は自動で参加)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:50'],
            'member_count' => ['required', 'integer', 'min:2', 'max:20'],
        ]);

        $book = Book::create([
            'title'        => $data['title'],
            'member_limit' => $data['member_count'],
            'code'         => Book::generateCode(),
            'owner_id'     => Auth::id(),
        ]);

        $book->members()->attach(Auth::id(), ['last_read_at' => now()]);

        return redirect()->route('bookshells.index');
    }

    // コードから参加できる本を探す(共通の判定)
    private function findJoinable(string $code): array
    {
        $book = Book::where('code', strtoupper(trim($code)))->first();

        if (!$book) {
            return [null, 'コードが見つかりません'];
        }

        if ($book->members()->where('users.id', Auth::id())->exists()) {
            return [null, 'すでに参加しています'];
        }

        if ($book->members()->count() >= $book->member_limit) {
            return [null, 'この本は参加人数がいっぱいです'];
        }

        return [$book, null];
    }

    // 1段階目:コードを確認して、本の名前を画面に返す(まだ参加しない)
    public function joinCheck(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:8']]);

        [$book, $error] = $this->findJoinable($request->code);

        if (!$book) {
            return back()->withErrors(['code' => $error])->withInput();
        }

        return back()->with('found', ['code' => $book->code, 'title' => $book->title]);
    }

    // 2段階目:「参加」を押したときに、実際に参加する
    public function join(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:8']]);

        [$book, $error] = $this->findJoinable($request->code);

        if (!$book) {
            return back()->withErrors(['code' => $error]);
        }

        $book->members()->attach(Auth::id(), ['last_read_at' => now()]);

        return redirect()->route('bookshells.index');
    }

    // 本棚から削除(自分の本棚から外れる。全員が外れたら本も削除)
    public function destroy(Request $request)
    {
        $data = $request->validate(['book_id' => ['required', 'integer']]);

        $book = Book::find($data['book_id']);

        // 参加していない本、存在しない本は、何もせず戻る
        if ($book && $book->members()->where('users.id', Auth::id())->exists()) {
            $book->members()->detach(Auth::id());

            if ($book->members()->count() === 0) {
                $book->delete();
            }
        }

        return redirect()->route('bookshells.index');
    }
}