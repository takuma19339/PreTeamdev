<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiaryController extends Controller
{
    // タブの色の候補(参加順に割り当てる)
    private const COLORS = ['#c0282d', '#464866', '#7a4f67', '#4f7a4f', '#8a7a35', '#3d5f7a', '#a34a2c', '#2f6a6a'];

    /**
     * 本ごとの日記を開く。
     * この本の日記・コメント・やることを、まとめて画面(Alpine.js)に渡す。
     */
    public function show(Book $book): View
    {
        $this->authorizeMember($book);

        // この本を開いたので、既読にする(本棚の○が消える)
        $book->members()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        // タブ = 参加メンバー。自分のタブを先頭にする。
        // 色は inline style で渡す(Tailwindのpurge対策)
        $members = $book->members()->orderBy('book_user.created_at')->get()
            ->sortBy(fn ($user) => $user->id === Auth::id() ? 0 : 1)
            ->values()
            ->map(fn ($user, $i) => [
                'id' => $user->id,
                'key' => 'member-' . $user->id,
                'name' => $user->name,
                'color' => self::COLORS[$i % count(self::COLORS)],
                'is_me' => $user->id === Auth::id(),
            ])
            ->all();

        // 日記(コメントつき)。キーは 'member-ユーザーid|YYYY-MM-DD'
        $entries = $book->diaryEntries()
            ->with(['comments' => fn ($q) => $q->orderBy('id'), 'comments.user:id,name'])
            ->get()
            ->mapWithKeys(fn ($entry) => [
                'member-' . $entry->user_id . '|' . $entry->date => [
                    'weather' => $entry->weather ?? '',
                    'body' => $entry->body ?? '',
                    'comments' => $entry->comments->map(fn ($comment) => [
                        'id' => $comment->id,
                        'name' => $comment->user?->name ?? '退会したユーザー',
                        'body' => $comment->body,
                    ])->all(),
                ],
            ])
            ->all();

        $todos = $book->todos()
            ->orderBy('id')
            ->get()
            ->map(fn ($todo) => $todo->toFront())
            ->all();

        $config = [
            'base' => route('diary.show', $book, false),   // 保存用URLのもと(例: /bookshells/1)。相対パスなので APP_URL に左右されない
            'members' => $members,
            // 招待コード(友だちに伝えて、本棚の「コードで参加」に入れてもらう)
            'invite' => [
                'code' => $book->code,
                'count' => count($members),
                'limit' => $book->member_limit,
            ],
            'entries' => (object) $entries,         // 空でも {} になるように object にする
            'todos' => $todos,
        ];

        return view('diary.diary', compact('book', 'config'));
    }

    /**
     * 自分の日記を保存する(その日の分がなければ作る)。
     * PUT /bookshells/{book}/diary/{date}
     */
    public function saveEntry(Request $request, Book $book, string $date): JsonResponse
    {
        $this->authorizeMember($book);
        $this->validateDate($date);

        $data = $request->validate([
            'weather' => ['nullable', Rule::in(['sunny', 'cloudy', 'rainy'])],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        DiaryEntry::updateOrCreate(
            ['book_id' => $book->id, 'user_id' => Auth::id(), 'date' => $date],
            ['weather' => $data['weather'] ?? null, 'body' => $data['body'] ?? null],
        );

        return response()->json(['saved' => true]);
    }

    /**
     * だれかの日記にコメントする(日記がまだなければ、空の日記を作ってつなぐ)。
     * POST /bookshells/{book}/diary/{owner}/{date}/comments
     */
    public function storeComment(Request $request, Book $book, User $owner, string $date): JsonResponse
    {
        $this->authorizeMember($book);
        abort_unless($book->members()->where('users.id', $owner->id)->exists(), 404);
        $this->validateDate($date);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $entry = DiaryEntry::firstOrCreate([
            'book_id' => $book->id,
            'user_id' => $owner->id,
            'date' => $date,
        ]);

        $comment = $entry->comments()->create([
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        return response()->json([
            'id' => $comment->id,
            'name' => Auth::user()->name,
            'body' => $comment->body,
        ], 201);
    }

    // この本に参加している人だけが使える
    private function authorizeMember(Book $book): void
    {
        abort_unless($book->members()->where('users.id', Auth::id())->exists(), 403);
    }

    // 日付は 'YYYY-MM-DD' だけ受け付ける
    private function validateDate(string $date): void
    {
        Validator::make(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();
    }
}