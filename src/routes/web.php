<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookshellController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/bookshells', [BookshellController::class, 'index'])->name('bookshells.index');
    Route::post('/bookshells', [BookshellController::class, 'store'])->name('bookshells.store');
    Route::post('/bookshells/join/check', [BookshellController::class, 'joinCheck'])->name('bookshells.join.check');
    Route::post('/bookshells/join', [BookshellController::class, 'join'])->name('bookshells.join');
    Route::delete('/bookshells', [BookshellController::class, 'destroy'])->name('bookshells.destroy');

    // 本ごとの手帳(本棚の本を押すと開く)
    Route::get('/bookshells/{book}', [DiaryController::class, 'show'])
    ->whereNumber('book')
    ->name('diary.show');

    // 日記の保存・コメント
    Route::put('/bookshells/{book}/diary/{date}', [DiaryController::class, 'saveEntry'])
        ->whereNumber('book')->name('diary.save');
    Route::post('/bookshells/{book}/diary/{owner}/{date}/comments', [DiaryController::class, 'storeComment'])
        ->whereNumber(['book', 'owner'])->name('diary.comment');

    // やること(DB連携)
    Route::post('/bookshells/{book}/todos', [TodoController::class, 'store'])
        ->whereNumber('book')->name('todos.store');
    Route::patch('/bookshells/{book}/todos/{todo}', [TodoController::class, 'update'])
        ->whereNumber(['book', 'todo'])->name('todos.update');
    Route::delete('/bookshells/{book}/todos/{todo}', [TodoController::class, 'destroy'])
        ->whereNumber(['book', 'todo'])->name('todos.destroy');
});