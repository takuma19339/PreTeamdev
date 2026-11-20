<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    // ログイン画面を表示する
    public function create(): View
    {
        return view('auth.login');
    }

    // ログインする
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            // メールとパスワードのどちらが違うかは、あえて教えない(安全のため)
            return back()
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // ログイン前に開こうとしていたページがあればそこへ、なければ本棚へ
        return redirect()->intended(route('bookshells.index'));
    }

    // ログアウトする
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}