<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>交換日記の本棚</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Old+Mincho&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Zen Old Mincho', serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

@php
    // 本 + 「新しい本」を1段5冊ずつに分け、最低3段は表示する
    $shelves = collect($books)->push(['new' => true])->chunk(5);
    $shelfCount = max(3, $shelves->count());
@endphp

<body class="min-h-screen bg-gradient-to-b from-[#faf0d9] to-[#e8c99c] text-[#4a2c12]"
      x-data="{
          open: {{ (session('found') || $errors->has('code')) ? 'true' : 'false' }},
          create: false,
          join: {{ $errors->has('code') ? 'true' : 'false' }},
          found: {{ session('found') ? 'true' : 'false' }},
          editing: false,
          del: null
      }"
      @keydown.escape.window="del ? del = null : (found ? found = false : (create ? create = false : (join ? join = false : open = false)))"
      x-effect="document.body.classList.toggle('overflow-hidden', open || create || join || found || !!del)">

    <main class="relative mx-auto max-w-md px-5 py-12">

        {{-- 編集ボタン(右上) --}}
        <button type="button" @click="editing = !editing"
                class="absolute right-5 top-5 rounded-full border px-4 py-1.5 text-sm tracking-widest transition"
                :class="editing
                    ? 'border-[#9a6238] bg-[#9a6238] text-[#faf0d9] hover:bg-[#8a5530]'
                    : 'border-[#b07a4a] text-[#8a6a4a] hover:bg-[#d6c28f]/60'">
            <span x-text="editing ? '完了' : '編集'">編集</span>
        </button>

        {{-- タイトル --}}
        <header class="mb-10 text-center">
            <h1 class="text-3xl tracking-[0.25em]">交換日記の本棚</h1>
            <p class="mt-4 text-sm tracking-[0.2em] text-[#8a6a4a]"
               x-text="editing ? '外したい本の「−」を押してください' : '開く日記帳をえらんでください'">
                開く日記帳をえらんでください
            </p>
        </header>

        {{-- 本棚の枠 --}}
        <div class="rounded-xl border-[14px] border-[#9a6238] bg-[#4a2c1c] px-3 pt-8 shadow-[8px_8px_0_rgba(80,50,25,0.35)]">

            @for ($i = 0; $i < $shelfCount; $i++)
                @php $row = $shelves->get($i, collect()); @endphp

                {{-- 1段分(本を下揃えで並べる) --}}
                <div class="flex h-64 items-end gap-2 px-3">
                    @foreach ($row as $book)
                        @if (!empty($book['new']))
                            {{-- 新しい本(押すとメニューが開く) --}}
                            <button type="button"
                                    @click="open = true"
                                    class="flex h-[215px] w-16 flex-col items-center justify-center gap-2 rounded-sm border-2 border-dashed border-[#d9c3a0]/70 bg-[#5a3d2c] text-[#d9c3a0] transition hover:bg-[#6b4a35]">
                                <span class="text-3xl leading-none">＋</span>
                                <span class="text-sm tracking-widest [writing-mode:vertical-rl]">新しい本</span>
                            </button>
                        @else
                            {{-- 日記帳(背表紙)+ 編集モードの「−」ボタン --}}
                            <div class="relative">
                                <a href="{{ route('diary.show', $book['id']) }}"
                                   @click="if (editing) $event.preventDefault()"
                                   class="flex w-12 items-center justify-center rounded-r-sm border-r-2 border-[#f5e6c8] py-5 text-lg tracking-widest text-[#f5e6c8] transition hover:-translate-y-1 [writing-mode:vertical-rl]"
                                   style="height: {{ $book['height'] }}px; background-color: {{ $book['color'] }};">
                                    {{ $book['title'] }}
                                </a>

                                <button type="button" x-show="editing" x-cloak
                                        @click="del = @js(['id' => $book['id'], 'title' => $book['title']])"
                                        aria-label="{{ $book['title'] }}を本棚から外す"
                                        class="absolute -left-2 -top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-[#c8574a] text-base leading-none text-white shadow">
                                    −
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- 棚板 --}}
                <div class="h-3.5 rounded-sm bg-gradient-to-b from-[#b07a4a] to-[#8f5a30] shadow-md"></div>
            @endfor

        </div>
    </main>

    {{-- 背景の暗幕(押すと閉じる) --}}
    <div x-show="open" x-cloak
         x-transition.opacity.duration.300ms
         @click="open = false"
         class="fixed inset-0 z-40 bg-black/30"></div>

    {{-- 下から出るメニュー --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         role="dialog" aria-modal="true" aria-label="新しい本を追加"
         class="fixed inset-x-0 bottom-0 z-50 mx-auto max-w-md rounded-t-[2.5rem] bg-[#e0cfa3] px-8 pb-8 pt-10 shadow-[0_-8px_30px_rgba(60,35,15,0.3)]">

        <h2 class="mx-auto w-fit border-b border-[#8a6a4a] px-6 pb-2 text-xl tracking-widest">
            新しい本を追加
        </h2>

        <div class="mt-9 flex flex-col gap-9">
            {{-- 本を作成 --}}
            <button type="button"
                    @click="create = true"
                    class="block w-full rounded-lg border border-[#b07a4a] px-5 py-5 text-left text-sm tracking-wider transition hover:bg-[#d6c28f]">
                本を作成
            </button>

            {{-- コードで参加 --}}
            <button type="button"
                    @click="join = true"
                    class="block w-full rounded-lg border border-[#b07a4a] px-5 py-5 text-left text-sm tracking-wider transition hover:bg-[#d6c28f]">
                コードで参加
            </button>
        </div>

        <button type="button" @click="open = false"
                class="mx-auto mt-6 block text-sm tracking-widest text-[#8a6a4a]">
            閉じる
        </button>
    </div>

    {{-- 新しい本カードの暗幕(下メニューより手前) --}}
    <div x-show="create" x-cloak
         x-transition.opacity.duration.300ms
         @click="create = false"
         class="fixed inset-0 z-[60] bg-black/30"></div>

    {{-- 新しい本カード --}}
    <div x-show="create" x-cloak
         class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
        <div x-show="create"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0"
             x-effect="if (create) $nextTick(() => $refs.title.focus())"
             role="dialog" aria-modal="true" aria-label="新しい本"
             class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-8 pb-12 pt-8 shadow-xl">

            {{-- 閉じる(×) --}}
            <button type="button" @click="create = false" aria-label="閉じる"
                    class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">
                ×
            </button>

            <h2 class="text-center text-xl tracking-widest">新しい本</h2>

            <form method="POST" action="{{ route('bookshells.store') }}" class="mt-8 space-y-4">
                @csrf

                <input type="text" name="title" x-ref="title" required maxlength="50"
                       placeholder="タイトル"
                       class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm placeholder:text-[#4a2c12]/80 focus:ring-2 focus:ring-[#b07a4a]">

                <input type="number" name="member_count" required min="2" max="20"
                       placeholder="参加人数"
                       class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm placeholder:text-[#4a2c12]/80 focus:ring-2 focus:ring-[#b07a4a]">

                <div class="pt-4 text-center">
                    <button type="submit"
                            class="rounded-lg bg-[#a8987f] px-6 py-2 text-sm tracking-wider text-[#4a2c12] transition hover:bg-[#968669]">
                        ＋作成する
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- コードで参加カードの暗幕 --}}
    <div x-show="join" x-cloak
         x-transition.opacity.duration.300ms
         @click="join = false"
         class="fixed inset-0 z-[60] bg-black/30"></div>

    {{-- コードで参加カード --}}
    <div x-show="join" x-cloak
         class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
        <div x-show="join"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0"
             x-effect="if (join) $nextTick(() => $refs.code.focus())"
             role="dialog" aria-modal="true" aria-label="コードで参加"
             class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-8 pb-10 pt-8 shadow-xl">

            <button type="button" @click="join = false" aria-label="閉じる"
                    class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">
                ×
            </button>

            <h2 class="text-center text-xl tracking-widest">コードで参加</h2>

            <form method="POST" action="{{ route('bookshells.join.check') }}" class="mt-8">
                @csrf

                <div class="flex items-center rounded-full bg-white py-1 pl-5 pr-3">
                    <input type="text" name="code" x-ref="code" required
                           maxlength="8" autocomplete="off" autocapitalize="characters"
                           value="{{ old('code') }}"
                           placeholder="コードを入力"
                           class="w-full border-0 bg-transparent p-0 py-2 text-sm uppercase placeholder:normal-case placeholder:text-[#4a2c12]/80 focus:ring-0">
                    <button type="submit" aria-label="確認する"
                            class="ml-2 text-2xl leading-none text-[#4a2c12]">→</button>
                </div>

                @error('code')
                    <p class="mt-3 text-center text-xs text-[#a33a2c]">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>

    {{-- 参加の確認カードの暗幕 --}}
    <div x-show="found" x-cloak
         x-transition.opacity.duration.300ms
         @click="found = false"
         class="fixed inset-0 z-[80] bg-black/30"></div>

    {{-- 参加の確認カード --}}
    <div x-show="found" x-cloak
         class="pointer-events-none fixed inset-0 z-[90] flex items-center justify-center p-6">
        <div x-show="found"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0"
             role="dialog" aria-modal="true" aria-label="本に参加"
             class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-8 pb-8 pt-8 shadow-xl">

            <button type="button" @click="found = false" aria-label="閉じる"
                    class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">
                ×
            </button>

            <h2 class="mt-10 text-center text-lg tracking-widest">{{ session('found.title') }}</h2>

            <form method="POST" action="{{ route('bookshells.join') }}" class="mt-6 text-center">
                @csrf
                <input type="hidden" name="code" value="{{ session('found.code') }}">
                <button type="submit"
                        class="rounded-full bg-[#c8574a] px-8 py-1.5 text-sm tracking-widest text-[#4a2c12] transition hover:bg-[#b84c40]">
                    参加
                </button>
            </form>
        </div>
    </div>

    {{-- 削除の確認カードの暗幕 --}}
    <div x-show="del" x-cloak
         x-transition.opacity.duration.300ms
         @click="del = null"
         class="fixed inset-0 z-[60] bg-black/30"></div>

    {{-- 削除の確認カード --}}
    <div x-show="del" x-cloak
         class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
        <div x-show="del"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0"
             role="dialog" aria-modal="true" aria-label="本棚から削除"
             class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-8 pb-8 pt-8 shadow-xl">

            <button type="button" @click="del = null" aria-label="閉じる"
                    class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">
                ×
            </button>

            <h2 class="mt-10 text-center text-lg tracking-widest" x-text="del ? del.title : ''"></h2>
            <p class="mt-3 text-center text-sm text-[#8a6a4a]">この本を本棚から削除しますか?</p>
            <p class="mt-2 text-center text-xs leading-relaxed text-[#8a6a4a]">
                ほかのメンバーの本棚には残ります。<br>
                全員が削除すると、本は完全に消えます。
            </p>

            <form method="POST" action="{{ route('bookshells.destroy') }}"
                  class="mt-6 flex justify-center gap-4">
                @csrf
                @method('DELETE')
                <input type="hidden" name="book_id" :value="del ? del.id : ''">
                <button type="button" @click="del = null"
                        class="rounded-full border border-[#b07a4a] px-6 py-1.5 text-sm tracking-widest text-[#4a2c12] transition hover:bg-[#d6c28f]">
                    やめる
                </button>
                <button type="submit"
                        class="rounded-full bg-[#c8574a] px-8 py-1.5 text-sm tracking-widest text-[#4a2c12] transition hover:bg-[#b84c40]">
                    削除
                </button>
            </form>
        </div>
    </div>

</body>
</html>