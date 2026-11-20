{{--
  入力欄の部品。使い方:
  <x-auth.field name="email" type="email" label="メールアドレス" icon="mail" autocomplete="email" />
  icon は user / mail / lock のどれか。type="password" のときは目のボタンが付く。
--}}
@props(['name', 'label', 'type' => 'text', 'icon' => 'user', 'autocomplete' => 'off'])

<div>
  <label for="{{ $name }}" class="sr-only">{{ $label }}</label>
  <div class="field" @error($name) style="border-color:#9b2c2c" @enderror>
    @switch($icon)
      @case('mail')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="currentColor"/><path d="m4 7 8 6 8-6" stroke="#fcf4e4"/></svg>
        @break
      @case('lock')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10V7.5a5 5 0 0 1 10 0V10h-2.5V7.5a2.5 2.5 0 0 0-5 0V10Z"/><rect x="4.5" y="10" width="15" height="11" rx="2.5"/></svg>
        @break
      @default
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4.5"/><path d="M3.5 21c0-5 3.5-7.5 8.5-7.5s8.5 2.5 8.5 7.5Z"/></svg>
    @endswitch

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           placeholder="{{ $label }}" autocomplete="{{ $autocomplete }}" required
           @if ($type !== 'password') value="{{ old($name) }}" @endif
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>

    @if ($type === 'password')
      <button type="button" class="eye-btn" data-toggle-password="{{ $name }}" aria-label="パスワードを表示する" aria-pressed="false">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path d="M4 4l16 16"/></svg>
      </button>
    @endif
  </div>

  @error($name)
    <p id="{{ $name }}-error" class="error-text mt-1 px-1" role="alert">{{ $message }}</p>
  @enderror
</div>