@extends('layouts.auth')

@section('title', 'ユーザー登録')

@section('content')
  <h2 class="page-title">ユーザー登録</h2>
  <div class="divider"><i></i></div>
  <p class="mt-5 text-center text-base leading-8 tracking-[0.08em]">交換日記をはじめるために、<br>まずはアカウントを作成しましょう</p>

  <form method="POST" action="{{ route('register') }}" class="mt-6 flex flex-col gap-4">
    @csrf

    <x-auth.field name="name" label="ユーザー名" icon="user" autocomplete="nickname" />
    <x-auth.field name="email" type="email" label="メールアドレス" icon="mail" autocomplete="email" />
    <x-auth.field name="password" type="password" label="パスワード(8文字以上)" icon="lock" autocomplete="new-password" />
    <x-auth.field name="password_confirmation" type="password" label="パスワード(確認用)" icon="lock" autocomplete="new-password" />


    <button type="submit" class="btn-primary">登録する</button>
  </form>

  <div class="divider mt-6"><i></i></div>
  <p class="text-center"><a href="{{ route('login') }}" class="text-link inline-flex min-h-11 items-center">すでにアカウントをお持ちの方はこちら ›</a></p>
@endsection