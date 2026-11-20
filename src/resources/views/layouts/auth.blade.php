<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | 交換日記の本棚</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <main class="relative mx-auto w-full max-w-md px-4 pb-12 pt-10">
    {{-- ロゴ --}}
    <header class="text-center">
      <svg class="mx-auto" width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="#6b3f22" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M32 20v28M32 48C26 42 14 42 8 44V22c6-2 18-2 24 4 6-6 18-6 24-4v22c-6-2-18-2-24 4Z"/>
        <path d="M32 6v6M22 9l3 5M42 9l-3 5"/>
      </svg>
      <h1 class="mt-3 text-[34px] font-medium tracking-[0.18em]">交換日記の本棚</h1>
      <p class="mt-2 text-sm tracking-[0.12em]">開く日記帳をえらんでください</p>
    </header>

    <div class="relative mt-10">
      {{-- カードの後ろからのぞく背表紙(飾り) --}}
      <div class="absolute bottom-full left-[-8px] -mb-3 flex items-end gap-0.5" aria-hidden="true">
        <div class="spine h-20 w-9 bg-[#5a3320]"></div>
        <div class="spine h-14 w-8 bg-[#a4532f]"></div>
        <div class="spine h-11 w-8 bg-[#4f7390]"></div>
      </div>
      <div class="absolute bottom-full right-[-8px] -mb-3 flex items-end gap-0.5" aria-hidden="true">
        <div class="spine h-12 w-9 bg-[#5f8a4f]"></div>
        <div class="spine h-[72px] w-9 bg-[#8e3a4a]"></div>
      </div>

      <section class="book-card">
        <div class="book-page">
          @yield('content')
        </div>
      </section>
    </div>
  </main>
  <script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>