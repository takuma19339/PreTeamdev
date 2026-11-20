<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// ログインしていない人だけが見られるページ
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    // 総当たり攻撃を防ぐため、1分間に5回までにする
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// ログインしている人だけが見られるページ
Route::middleware('auth')->group(function () {
    // TODO: 本棚の画面ができたら、ここをコントローラーに置き換える
    Route::get('/bookshelf', fn () => '本棚の画面がここに入ります')->name('bookshelf');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});