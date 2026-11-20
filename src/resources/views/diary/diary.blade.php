<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $book->title }} | 日記</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;600&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="diaryPage(@js($config))"
    @keydown.escape.window="closeModals()"
    class="min-h-screen bg-[#f7ebd5] text-[#4a2f20] [font-family:'Shippori_Mincho',serif]"
>
<main class="mx-auto w-full max-w-md px-4 pb-12 pt-4">

    {{-- ヘッダー --}}
    <header class="flex items-center justify-between gap-3">
        <a href="{{ route('bookshells.index') }}" class="shrink-0 text-sm tracking-widest text-[#8a6a4a]">← 本棚</a>
        <span class="truncate rounded-xl bg-[#d4d4d8] px-5 py-1.5 text-lg">{{ $book->title }}</span>
    </header>

    {{-- 手帳本体 --}}
    <div class="relative mr-8 mt-8">
        <div class="rounded-2xl bg-[#5c3a28] py-3 pl-3 pr-2 shadow-lg">
            <div class="rounded-xl bg-[#7d5236] p-1.5">
                <div class="relative rounded-lg bg-[#e6d5b8] px-6 pb-6 pt-5 shadow-inner">

                    {{-- 左右の端:前の日 / 次の日(押すとその日の日記へ) --}}
                    <button type="button" x-show="view === 'diary'" @click="date = shift(date, -1)"
                            :aria-label="label(shift(date, -1)) + 'の日記を見る'"
                            class="absolute inset-y-6 left-0 flex w-5 items-center justify-center rounded-l-lg bg-[#d6c28f]/30 text-xs tracking-widest text-[#6b3f2a] transition hover:bg-[#d6c28f]/70 [writing-mode:vertical-rl]"
                            x-text="'‹ ' + short(shift(date, -1))"></button>
                    <button type="button" x-show="view === 'diary'" @click="date = shift(date, 1)"
                            :aria-label="label(shift(date, 1)) + 'の日記を見る'"
                            class="absolute inset-y-6 right-0 flex w-5 items-center justify-center rounded-r-lg bg-[#d6c28f]/30 text-xs tracking-widest text-[#6b3f2a] transition hover:bg-[#d6c28f]/70 [writing-mode:vertical-rl]"
                            x-text="short(shift(date, 1)) + ' ›'"></button>

                    {{-- ===== 日記ページ ===== --}}
                    <section x-show="view === 'diary'" role="tabpanel">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                {{-- 日付(押すとカレンダーが開く) --}}
                                <button type="button" @click="openCalendar()" aria-haspopup="dialog"
                                        class="flex items-baseline gap-1 text-left text-xl text-[#6b3f2a]">
                                    <span x-text="label(date)"></span>
                                    <span class="text-xs text-[#8a6a4a]">▾</span>
                                </button>
                                <p class="mt-1 text-xs"><span x-text="currentMember.name"></span>の日記</p>
                            </div>

                            {{-- 心の天気(自分の日記だけ選べる) --}}
                            <button type="button" @click="if (editable) weatherOpen = true" :disabled="!editable"
                                    aria-label="心の天気を選ぶ"
                                    class="flex h-14 w-[4.5rem] shrink-0 flex-col items-center justify-center rounded bg-[#d4d4d8] text-center disabled:cursor-default">
                                <span class="text-[10px] leading-none">心の天気</span>
                                <span class="mt-1 text-xl leading-none" x-show="current.weather" x-text="weatherEmoji(current.weather)"></span>
                                <span class="mt-1 text-[10px] leading-none text-[#4a2f20]/60" x-show="!current.weather"
                                      x-text="editable ? 'えらぶ' : '未記入'"></span>
                            </button>
                        </div>

                        <div class="mt-2 h-[3px] bg-[#3b2a1d]"></div>

                        {{-- 日記本文(罫線つきテキストエリア。1行 = 28px) --}}
                        <textarea
                            x-ref="body"
                            rows="6"
                            :readonly="!editable"
                            :placeholder="editable ? 'きょうのできごとを書こう' : 'まだ書かれていません'"
                            :value="current.body"
                            @input="ensure().body = $event.target.value; fit($event.target)"
                            :aria-label="currentMember.name + 'の日記'"
                            class="mt-2 block h-[168px] min-h-[168px] w-full resize-none overflow-hidden border-0 bg-transparent p-0 text-sm leading-7 placeholder:text-[#4a2f20]/40 focus:outline-none focus:ring-0 [background-attachment:local] [background-image:repeating-linear-gradient(to_bottom,transparent_0,transparent_27px,#8f7c5f_27px,#8f7c5f_28px)]"
                        ></textarea>

                        {{-- コメント一覧 --}}
                        <h3 class="mt-6 text-sm">コメント</h3>
                        <div class="mt-1 h-[3px] bg-[#3b2a1d]"></div>
                        <div class="mt-3 min-h-[80px] space-y-2 rounded-xl border-2 border-[#5c3a28]/70 bg-[#f7ecd8] p-3">
                            <template x-for="(c, i) in current.comments" :key="i">
                                <p class="text-xs leading-5">
                                    <span class="font-semibold" x-text="c.name"></span>
                                    <span class="ml-1" x-text="c.body"></span>
                                </p>
                            </template>
                            <p class="text-xs text-[#4a2f20]/50" x-show="current.comments.length === 0">まだコメントはありません</p>
                        </div>

                        {{-- コメント入力(画面上だけで動く。保存はまだ) --}}
                        <h3 class="mt-5 text-sm font-semibold">コメントを書く</h3>
                        <textarea
                            rows="3"
                            x-model="commentDraft"
                            aria-label="日記へのコメント"
                            class="mt-2 w-full resize-none rounded-xl border-2 border-[#5c3a28]/70 bg-[#f7ecd8] p-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7d5236]"
                        ></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="button" @click="addComment()" :disabled="!commentDraft.trim()"
                                    class="rounded-lg bg-[#5c3a28] px-4 py-1.5 text-xs text-[#f7ecd8] disabled:opacity-40">送信</button>
                        </div>
                    </section>

                    {{-- ===== やること ===== --}}
                    <section x-show="view === 'todo'" role="tabpanel">
                        <h2 class="text-xl text-[#6b3f2a]">みんなのやること</h2>
                        <p class="mt-1 text-xs">残り <span x-text="remainingTotal"></span> 件</p>
                        <div class="mt-2 h-[3px] bg-[#3b2a1d]"></div>

                        <div x-show="sharedTodos.length > 0">
                            <h3 class="mt-5 text-sm font-semibold">共有</h3>
                            <ul class="mt-2 space-y-2">
                                <template x-for="t in sharedTodos" :key="t.id">
                                    <li class="flex items-start gap-3 rounded-lg bg-[#f7ecd8] px-3 py-2">
                                        <button type="button" @click="t.done = !t.done" :aria-pressed="t.done" aria-label="完了にする"
                                                class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 border-[#5c3a28] text-[10px] leading-none"
                                                x-text="t.done ? '✓' : ''"></button>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm" :class="t.done ? 'line-through opacity-50' : ''" x-text="t.title"></p>
                                            <p class="mt-0.5 text-[10px] text-[#4a2f20]/70" x-show="meta(t)" x-text="meta(t)"></p>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div x-show="personalTodos.length > 0">
                            <h3 class="mt-5 text-sm font-semibold">個人</h3>
                            <ul class="mt-2 space-y-2">
                                <template x-for="t in personalTodos" :key="t.id">
                                    <li class="flex items-start gap-3 rounded-lg bg-[#f7ecd8] px-3 py-2">
                                        <button type="button" @click="t.done = !t.done" :aria-pressed="t.done" aria-label="完了にする"
                                                class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 border-[#5c3a28] text-[10px] leading-none"
                                                x-text="t.done ? '✓' : ''"></button>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm" :class="t.done ? 'line-through opacity-50' : ''" x-text="t.title"></p>
                                            <p class="mt-0.5 text-[10px] text-[#4a2f20]/70" x-show="meta(t)" x-text="meta(t)"></p>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <p class="mt-5 text-center text-xs text-[#4a2f20]/50" x-show="todos.length === 0">まだやることはありません</p>

                        {{-- やることリストの下の追加ボタン --}}
                        <button type="button" @click="openTodo()"
                                class="mt-6 w-full rounded border border-dashed border-[#5c3a28]/70 py-2 text-sm tracking-wider transition hover:bg-[#d6c28f]/50">
                            ＋やることを追加
                        </button>
                    </section>

                </div>
            </div>
        </div>

        {{-- 右側のインデックスタブ --}}
        <nav role="tablist" aria-label="日記のタブ" class="absolute left-full top-6 -ml-1 flex flex-col gap-1">
            @foreach ($config['members'] as $member)
                <button
                    type="button"
                    role="tab"
                    @click="view = 'diary'; memberKey = '{{ $member['key'] }}'"
                    :aria-selected="view === 'diary' && memberKey === '{{ $member['key'] }}'"
                    :class="view === 'diary' && memberKey === '{{ $member['key'] }}' ? 'w-10' : 'w-8'"
                    style="background-color: {{ $member['color'] }}"
                    class="flex h-24 items-center justify-center rounded-r-md text-sm tracking-[0.35em] text-white shadow transition-all [writing-mode:vertical-rl]"
                >{{ $member['name'] }}</button>
            @endforeach

            <button
                type="button"
                role="tab"
                @click="view = 'todo'"
                :aria-selected="view === 'todo'"
                :class="view === 'todo' ? 'w-10' : 'w-8'"
                style="background-color: #a99bc7"
                class="relative flex h-24 items-center justify-center rounded-r-md text-sm tracking-[0.35em] text-white shadow transition-all [writing-mode:vertical-rl]"
            >
                やること
                <span x-show="remainingTotal > 0"
                      class="absolute -left-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] leading-none tracking-normal text-white [writing-mode:horizontal-tb]">!</span>
            </button>
        </nav>
    </div>

    {{-- 下部カード --}}
    <div class="mt-8 grid grid-cols-2 gap-3">
        <section class="rounded-2xl bg-[#e3cfae] p-3 shadow">
            <h2 class="border-b border-[#4a2f20]/40 pb-1 text-center text-xs">近づいている締め切り</h2>
            <ul class="mt-2 space-y-2">
                <template x-for="t in deadlines" :key="t.id">
                    <li class="relative rounded-md bg-[#f4c6cc] px-2 py-1.5">
                        <span class="absolute right-1 top-1 rounded-full bg-red-600 px-1.5 py-0.5 text-[9px] leading-none text-white" x-text="remainingLabel(t.due)"></span>
                        <p class="text-[9px] leading-none" x-text="dueShort(t.due)"></p>
                        <p class="mt-1 pr-8 text-[10px] leading-tight" x-text="t.title"></p>
                    </li>
                </template>
            </ul>
            <p class="mt-3 text-center text-[10px] text-[#4a2f20]/60" x-show="deadlines.length === 0">締め切りはありません</p>
        </section>

        <section class="rounded-2xl bg-[#e3cfae] p-3 shadow">
            <h2 class="border-b border-[#4a2f20]/40 pb-1 text-center text-xs">残りのやること</h2>
            <div class="mt-3 flex items-center justify-around">
                <span class="text-5xl leading-none text-red-600" x-text="remainingTotal"></span>
                <dl class="space-y-1 text-xs">
                    <div class="flex items-baseline gap-3">
                        <dt>共有</dt>
                        <dd class="text-lg leading-none text-red-600" x-text="remainingShared"></dd>
                    </div>
                    <div class="flex items-baseline gap-3">
                        <dt>個人</dt>
                        <dd class="text-lg leading-none text-red-600" x-text="remainingPersonal"></dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>
</main>

{{-- ===== 心の天気モーダル ===== --}}
<div x-show="weatherOpen" x-cloak
     x-transition.opacity.duration.300ms
     @click="weatherOpen = false"
     class="fixed inset-0 z-[60] bg-black/30"></div>
<div x-show="weatherOpen" x-cloak
     class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
    <div x-show="weatherOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         role="dialog" aria-modal="true" aria-label="心の天気"
         class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-6 pb-8 pt-8 shadow-xl">
        <button type="button" @click="weatherOpen = false" aria-label="閉じる"
                class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">×</button>
        <h2 class="text-center text-xl tracking-widest">心の天気</h2>
        <p class="mt-2 text-center text-xs text-[#8a6a4a]">きょうの気分をえらんでください</p>

        <div class="mt-6 grid grid-cols-3 gap-3">
            <template x-for="w in weathers" :key="w.value">
                <button type="button" @click="setWeather(w.value)"
                        :class="current.weather === w.value ? 'border-[#9a6238] bg-[#e8d3a8]' : 'border-[#b07a4a]/50 bg-white/60'"
                        class="flex flex-col items-center gap-1 rounded-2xl border-2 py-4 transition">
                    <span class="text-3xl" x-text="w.emoji"></span>
                    <span class="text-sm tracking-widest" x-text="w.label"></span>
                </button>
            </template>
        </div>

        <button type="button" @click="setWeather('')" x-show="current.weather"
                class="mx-auto mt-5 block text-xs tracking-widest text-[#8a6a4a]">選択をはずす</button>
    </div>
</div>

{{-- ===== カレンダーモーダル ===== --}}
<div x-show="calendarOpen" x-cloak
     x-transition.opacity.duration.300ms
     @click="calendarOpen = false"
     class="fixed inset-0 z-[60] bg-black/30"></div>
<div x-show="calendarOpen" x-cloak
     class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
    <div x-show="calendarOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         role="dialog" aria-modal="true" aria-label="日付をえらぶ"
         class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-5 pb-6 pt-8 shadow-xl">
        <button type="button" @click="calendarOpen = false" aria-label="閉じる"
                class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">×</button>

        <div class="flex items-center justify-center gap-6">
            <button type="button" @click="moveMonth(-1)" aria-label="前の月" class="px-2 text-2xl leading-none">‹</button>
            <h2 class="min-w-[7rem] text-center text-lg tracking-widest" x-text="cal.year + '年' + (cal.month + 1) + '月'"></h2>
            <button type="button" @click="moveMonth(1)" aria-label="次の月" class="px-2 text-2xl leading-none">›</button>
        </div>

        <div class="mt-4 grid grid-cols-7 text-center text-xs text-[#8a6a4a]">
            <template x-for="w in weekdays" :key="w">
                <div class="py-1" x-text="w"></div>
            </template>
        </div>
        <div class="grid grid-cols-7 gap-y-1 text-center text-sm">
            <template x-for="(c, i) in calCells" :key="i">
                <div class="flex justify-center">
                    <template x-if="c">
                        <button type="button" @click="pick(c)"
                                :class="c === date ? 'bg-[#9a6238] text-[#faf0d9]' : (c === today ? 'ring-2 ring-[#b07a4a]' : 'hover:bg-[#d6c28f]/60')"
                                class="relative h-9 w-9 rounded-full transition">
                            <span x-text="Number(c.slice(8))"></span>
                            <span x-show="hasEntry(c)" class="absolute bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-[#c0282d]"></span>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <button type="button" @click="pick(today)"
                class="mx-auto mt-4 block rounded-full border border-[#b07a4a] px-5 py-1 text-xs tracking-widest transition hover:bg-[#d6c28f]">今日にもどる</button>
    </div>
</div>

{{-- ===== やること追加モーダル ===== --}}
<div x-show="todoOpen" x-cloak
     x-transition.opacity.duration.300ms
     @click="todoOpen = false"
     class="fixed inset-0 z-[60] bg-black/30"></div>
<div x-show="todoOpen" x-cloak
     class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-6">
    <div x-show="todoOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         x-effect="if (todoOpen) $nextTick(() => $refs.todoTitle.focus())"
         role="dialog" aria-modal="true" aria-label="やることを追加"
         class="pointer-events-auto relative w-full max-w-sm rounded-3xl bg-[#f5e4c3] px-8 pb-8 pt-8 shadow-xl">
        <button type="button" @click="todoOpen = false" aria-label="閉じる"
                class="absolute left-5 top-5 text-3xl leading-none text-[#4a2c12]">×</button>
        <h2 class="text-center text-xl tracking-widest">やることを追加</h2>

        <form @submit.prevent="addTodo()" class="mt-6 space-y-4">
            <input type="text" x-ref="todoTitle" x-model="todoForm.title" required maxlength="100"
                   placeholder="やることの内容"
                   class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm placeholder:text-[#4a2c12]/80 focus:ring-2 focus:ring-[#b07a4a]">

            <label class="block">
                <span class="mb-1 block pl-3 text-xs text-[#8a6a4a]">アサイン</span>
                <select x-model="todoForm.assignee"
                        class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm focus:ring-2 focus:ring-[#b07a4a]">
                    <option value="all">全員(共有)</option>
                    @foreach ($config['members'] as $member)
                        <option value="{{ $member['key'] }}">{{ $member['name'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block pl-3 text-xs text-[#8a6a4a]">期限</span>
                <input type="date" x-model="todoForm.due"
                       class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm focus:ring-2 focus:ring-[#b07a4a]">
            </label>

            <div class="pt-2 text-center">
                <button type="submit" :disabled="!todoForm.title.trim()"
                        class="rounded-lg bg-[#a8987f] px-6 py-2 text-sm tracking-wider text-[#4a2c12] transition hover:bg-[#968669] disabled:opacity-40">
                    ＋追加する
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // 画面の動き(Alpine.js)。保存はまだしていないので、リロードすると内容は消える。
    document.addEventListener('alpine:init', () => {
        const EMPTY = { weather: '', body: '', comments: [] };

        Alpine.data('diaryPage', (config) => ({
            members: config.members,
            view: 'diary',                       // 'diary' | 'todo'
            memberKey: config.members[0].key,    // 開いている日記の持ち主
            today: '',
            date: '',
            entries: {},                         // 'メンバーkey|YYYY-MM-DD' => { weather, body, comments }
            todos: [],
            nextTodoId: 1,
            weatherOpen: false,
            calendarOpen: false,
            todoOpen: false,
            cal: { year: 0, month: 0 },
            commentDraft: '',
            todoForm: { title: '', assignee: 'all', due: '' },
            weekdays: ['日', '月', '火', '水', '木', '金', '土'],
            weathers: [
                { value: 'sunny', emoji: '☀️', label: '晴れ' },
                { value: 'cloudy', emoji: '☁️', label: '曇り' },
                { value: 'rainy', emoji: '🌧️', label: '雨' },
            ],

            init() {
                this.today = this.iso(new Date());
                this.date = this.today;
                this.todos = config.todos.map((t, i) => ({
                    id: i + 1,
                    title: t.title,
                    assignee: t.assignee,
                    due: t.dueInDays === null ? null : this.shift(this.today, t.dueInDays),
                    done: t.done,
                }));
                this.nextTodoId = this.todos.length + 1;

                const refit = () => this.$nextTick(() => this.fit(this.$refs.body));
                this.$watch('date', refit);
                this.$watch('memberKey', refit);
                this.$watch('view', refit);
                refit();
            },

            // ---- 日付まわり ----
            iso(d) {
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            },
            parse(s) {
                const [y, m, d] = s.split('-').map(Number);
                return new Date(y, m - 1, d);
            },
            shift(s, n) {
                const d = this.parse(s);
                d.setDate(d.getDate() + n);
                return this.iso(d);
            },
            label(s) {
                const d = this.parse(s);
                return (d.getMonth() + 1) + '月' + d.getDate() + '日（' + this.weekdays[d.getDay()] + '）';
            },
            short(s) {
                const d = this.parse(s);
                return (d.getMonth() + 1) + '/' + d.getDate();
            },
            dueShort(s) {
                const d = this.parse(s);
                return (d.getMonth() + 1) + '/' + d.getDate() + '(' + this.weekdays[d.getDay()] + ')';
            },

            // ---- 日記 ----
            get currentMember() {
                return this.members.find((m) => m.key === this.memberKey);
            },
            get me() {
                return this.members.find((m) => m.is_me) ?? this.members[0];
            },
            get editable() {
                return this.currentMember.is_me;   // 自分の日記だけ書ける
            },
            get current() {
                return this.entries[this.memberKey + '|' + this.date] ?? EMPTY;
            },
            ensure() {
                const k = this.memberKey + '|' + this.date;
                if (!this.entries[k]) {
                    this.entries[k] = { weather: '', body: '', comments: [] };
                }
                return this.entries[k];
            },
            hasEntry(s) {
                const e = this.entries[this.memberKey + '|' + s];
                return !!(e && (e.body.trim() || e.weather));
            },
            fit(el) {
                if (!el) return;
                el.style.height = 'auto';
                el.style.height = Math.max(168, el.scrollHeight) + 'px';
            },
            weatherEmoji(v) {
                return this.weathers.find((w) => w.value === v)?.emoji ?? '';
            },
            setWeather(v) {
                this.ensure().weather = v;
                this.weatherOpen = false;
            },
            addComment() {
                const body = this.commentDraft.trim();
                if (!body) return;
                this.ensure().comments.push({ name: this.me.name, body: body });
                this.commentDraft = '';
            },

            // ---- カレンダー ----
            openCalendar() {
                const d = this.parse(this.date);
                this.cal = { year: d.getFullYear(), month: d.getMonth() };
                this.calendarOpen = true;
            },
            moveMonth(n) {
                const d = new Date(this.cal.year, this.cal.month + n, 1);
                this.cal = { year: d.getFullYear(), month: d.getMonth() };
            },
            get calCells() {
                const lead = new Date(this.cal.year, this.cal.month, 1).getDay();
                const days = new Date(this.cal.year, this.cal.month + 1, 0).getDate();
                const cells = [];
                for (let i = 0; i < lead; i++) cells.push(null);
                for (let d = 1; d <= days; d++) {
                    cells.push(this.iso(new Date(this.cal.year, this.cal.month, d)));
                }
                return cells;
            },
            pick(s) {
                this.date = s;
                this.calendarOpen = false;
            },

            // ---- やること ----
            get sharedTodos() { return this.todos.filter((t) => t.assignee === 'all'); },
            get personalTodos() { return this.todos.filter((t) => t.assignee !== 'all'); },
            get remainingTodos() { return this.todos.filter((t) => !t.done); },
            get remainingTotal() { return this.remainingTodos.length; },
            get remainingShared() { return this.remainingTodos.filter((t) => t.assignee === 'all').length; },
            get remainingPersonal() { return this.remainingTotal - this.remainingShared; },
            get deadlines() {
                return this.remainingTodos
                    .filter((t) => t.due)
                    .sort((a, b) => a.due.localeCompare(b.due))
                    .slice(0, 2);
            },
            assigneeName(key) {
                if (key === 'all') return '全員';
                const m = this.members.find((x) => x.key === key);
                return m ? m.name : '';
            },
            meta(t) {
                const parts = [];
                if (t.assignee !== 'all') parts.push(this.assigneeName(t.assignee));
                if (t.due) parts.push(this.dueShort(t.due));
                return parts.join(' ・ ');
            },
            remainingLabel(due) {
                const diff = Math.round((this.parse(due) - this.parse(this.today)) / 86400000);
                if (diff < 0) return (-diff) + '日超過';
                if (diff === 0) return '今日';
                return 'あと' + diff + '日';
            },
            openTodo() {
                this.todoForm = { title: '', assignee: 'all', due: '' };
                this.todoOpen = true;
            },
            addTodo() {
                const title = this.todoForm.title.trim();
                if (!title) return;
                this.todos.push({
                    id: this.nextTodoId++,
                    title: title,
                    assignee: this.todoForm.assignee,
                    due: this.todoForm.due || null,
                    done: false,
                });
                this.todoOpen = false;
            },

            closeModals() {
                this.weatherOpen = false;
                this.calendarOpen = false;
                this.todoOpen = false;
            },
        }));
    });
</script>
</body>
</html>