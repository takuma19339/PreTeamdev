<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookshellController;
use App\Http\Controllers\DiaryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookshells', [BookshellController::class, 'index'])->name('bookshells.index');
    Route::post('/bookshells', [BookshellController::class, 'store'])->name('bookshells.store');
    Route::post('/bookshells/join/check', [BookshellController::class, 'joinCheck'])->name('bookshells.join.check');
    Route::post('/bookshells/join', [BookshellController::class, 'join'])->name('bookshells.join');
    Route::delete('/bookshells', [BookshellController::class, 'destroy'])->name('bookshells.destroy');

    // 本ごとの手帳(本棚の本を押すと開く)
    Route::get('/bookshells/{book}', [DiaryController::class, 'show'])
    ->whereNumber('book')
    ->name('diary.show');
});

