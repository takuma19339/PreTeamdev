<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DiaryController extends Controller
{
    // タブの色の候補(参加順に割り当てる)
    private const COLORS = ['#c0282d', '#464866', '#7a4f67', '#4f7a4f', '#8a7a35', '#3d5f7a', '#a34a2c', '#2f6a6a'];

    /**
     * 本ごとの日記を開く。
     * タブ(参加メンバー)は実データ。日記・コメント・やることは、画面側(Alpine.js)で
     * 動かしているだけで、まだ保存されない(リロードすると消える)。
     */
    public function show(Book $book): View
    {
        // 参加していない本は開けない
        abort_unless($book->members()->where('users.id', Auth::id())->exists(), 403);

        // タブ = 参加メンバー。自分のタブを先頭にする。
        // 色は inline style で渡す(Tailwindのpurge対策)
        $members = $book->members()->orderBy('book_user.created_at')->get()
            ->sortBy(fn ($user) => $user->id === Auth::id() ? 0 : 1)
            ->values()
            ->map(fn ($user, $i) => [
                'key' => 'member-' . $user->id,
                'name' => $user->name,
                'color' => self::COLORS[$i % count(self::COLORS)],
                'is_me' => $user->id === Auth::id(),
            ])
            ->all();

        // ---- ここから下はダミー(DB連携で置き換える) ----
        $keys = array_column($members, 'key');
        $pick = fn (int $i) => $keys[$i % count($keys)];

        // assignee: 'all' = 全員(共有) / メンバーの key = 個人
        // dueInDays: 今日から何日後が期限か(null = 期限なし)
        $todos = [
            ['title' => 'フッターを完成させる', 'assignee' => 'all', 'dueInDays' => 2, 'done' => false],
            ['title' => 'ヒーローセクションの画像差し替え', 'assignee' => 'all', 'dueInDays' => 4, 'done' => false],
            ['title' => '日程調整の画面をつなぐ', 'assignee' => 'all', 'dueInDays' => 5, 'done' => false],
            ['title' => 'READMEを書く', 'assignee' => 'all', 'dueInDays' => 6, 'done' => false],
            ['title' => 'マイページのアイコン確認', 'assignee' => $pick(0), 'dueInDays' => null, 'done' => false],
            ['title' => 'ログイン画面のデザイン', 'assignee' => 'all', 'dueInDays' => -1, 'done' => true],
        ];

        $config = [
            'members' => $members,
            'todos' => $todos,
        ];

        return view('diary.diary', compact('book', 'config'));
    }
}