@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')
  <h2 class="page-title">ログイン</h2>
  <div class="divider"><i></i></div>
  <p class="mt-5 text-center text-base leading-8 tracking-[0.08em]">登録したアカウントで<br>ログインしてください</p>

  <form method="POST" action="{{ route('login') }}" class="mt-6 flex flex-col gap-4">
    @csrf

    <x-auth.field name="email" type="email" label="メールアドレス" icon="mail" autocomplete="email" />
    <x-auth.field name="password" type="password" label="パスワード" icon="lock" autocomplete="current-password" />

    <button type="submit" class="btn-primary mt-2">ログイン</button>
  </form>

  <div class="divider mt-6"><i></i></div>

  <div class="mt-6 flex items-center gap-6 rounded-2xl border border-[#b08a65] bg-[#fcf4e4] px-6 py-4">
    <svg width="48" height="48" viewBox="0 0 48 48" fill="#6b3f22" aria-hidden="true"><path d="M40 6C26 6 16 14 14 28l-6 12 2 1 5-8c2 1 5 1 8 0 10-3 16-12 17-27Z"/></svg>
    <div>
      <p class="text-sm tracking-[0.08em]">はじめての方はこちら</p>
      <a href="{{ route('register') }}" class="text-link mt-1 inline-flex min-h-11 items-center text-xl">新規登録する ›</a>
    </div>
  </div>
@endsection