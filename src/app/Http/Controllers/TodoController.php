<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    /**
     * やることを追加する。
     * POST /bookshells/{book}/todos
     * assignee_id が null なら「全員(共有)」、ユーザーidなら、その人の個人のやること。
     */
    public function store(Request $request, Book $book): JsonResponse
    {
        $this->authorizeMember($book);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'assignee_id' => ['nullable', 'integer'],
            'due' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $assigneeId = $data['assignee_id'] ?? null;

        // 担当者は、この本のメンバーから選ぶ
        abort_if(
            $assigneeId !== null && ! $book->members()->where('users.id', $assigneeId)->exists(),
            422,
            '担当者はこの本のメンバーから選んでください',
        );

        $todo = Todo::create([
            'book_id' => $book->id,
            'created_by' => Auth::id(),
            'assignee_id' => $assigneeId,
            'title' => $data['title'],
            'due_date' => $data['due'] ?? null,
        ]);

        $book->touchActivity(Auth::id());
        return response()->json($todo->toFront(), 201);
    }

    /**
     * 完了 / 未完了を切り替える。
     * PATCH /bookshells/{book}/todos/{todo}
     */
    public function update(Request $request, Book $book, Todo $todo): JsonResponse
    {
        $this->authorizeMember($book);
        abort_unless($todo->book_id === $book->id, 404);

        $data = $request->validate([
            'done' => ['required', 'boolean'],
        ]);

        $todo->update(['is_done' => $data['done']]);

        $book->touchActivity(Auth::id());
        return response()->json($todo->toFront());
    }

    // 削除
    public function destroy(Book $book, Todo $todo): JsonResponse
    {
        $this->authorizeMember($book);
        abort_unless($todo->book_id === $book->id, 404);

        $todo->delete();

        $book->touchActivity(Auth::id());
        return response()->json(['ok' => true]);
    }

    // 参加していない本のやることは触れない
    private function authorizeMember(Book $book): void
    {
        abort_unless($book->members()->where('users.id', Auth::id())->exists(), 403);
    }
}