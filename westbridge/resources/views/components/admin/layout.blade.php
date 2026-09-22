@props(['title' => 'Dashboard', 'actions' => null])

@php
    $user = auth()->user();
    $unread = \App\Models\ContactMessage::query()->where('is_read', false)->count();
    $nav = collect([
        ['Dashboard', route('admin.dashboard'), 'admin.dashboard', 'spark', null],
        ['Products', route('admin.products.index'), 'admin.products.*', 'device', 'products.view'],
        ['Categories', route('admin.categories.index'), 'admin.categories.*', 'retail', 'categories.view'],
        ['Projects', route('admin.projects.index'), 'admin.projects.*', 'code', 'projects.view'],
        ['Messages', route('admin.messages.index'), 'admin.messages.*', 'mail', 'leads.view'],
        ['News', route('admin.posts.index'), 'admin.posts.*', 'news', 'content.view'],
        ['Team', route('admin.team.index'), 'admin.team.*', 'team', 'content.view'],
        ['Pages', route('admin.pages.index'), 'admin.pages.*', 'education', 'content.view'],
        ['Settings', route('admin.settings.edit'), 'admin.settings.*', 'network', 'settings.view'],
        ['Staff', route('admin.users.index'), 'admin.users.*', 'shield', 'users.view'],
    ])->filter(fn ($item) => $item[4] === null || $user->can($item[4]));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <style>[x-cloak]{display:none !important}</style>
    <title>{{ $title }} · Admin · {{ setting('company.name', 'WestBridge Technologies') }}</title>
    <link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-paper-100 text-paper-900 antialiased" x-data="{ nav: false }">

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy-900 text-navy-200 transition-transform duration-200 lg:translate-x-0"
        :class="nav && 'translate-x-0'"
    >
        <a href="{{ route('admin.dashboard') }}" class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <x-brand.logo variant="reverse" class="h-9" />
            <span class="rounded-xs bg-lime-500/15 px-1.5 py-0.5 font-mono text-[10px] uppercase tracking-wider text-lime-400">Admin</span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-5" aria-label="Admin">
            @foreach ($nav as [$label, $href, $pattern, $icon])
                @php $active = request()->routeIs($pattern); @endphp
                <a href="{{ $href }}" @class([
                    'group mb-1 flex items-center gap-3 rounded-xs px-3 py-2.5 font-display text-[15px] font-medium transition-colors',
                    'bg-white/10 text-white' => $active,
                    'text-navy-200 hover:bg-white/5 hover:text-white' => ! $active,
                ])>
                    <x-icons.wb :name="$icon" @class(['h-5 w-5', 'text-lime-400' => $active, 'text-navy-400 group-hover:text-lime-400' => ! $active]) />
                    <span class="flex-1">{{ $label }}</span>
                    @if ($label === 'Messages' && $unread > 0)
                        <span class="rounded-full bg-lime-500 px-2 py-0.5 font-mono text-[11px] font-semibold text-navy-900">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-xs px-3 py-2.5 text-small text-navy-200 transition-colors hover:bg-white/5 hover:text-white">
                <x-icons.wb name="arrow" class="h-4 w-4 -rotate-45" /> View website
            </a>
        </div>
    </aside>

    <div class="fixed inset-0 z-30 bg-navy-950/60 lg:hidden" x-show="nav" x-cloak @click="nav = false"></div>

    <div class="lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-paper-300 bg-white px-4 sm:px-8">
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xs text-navy-700 hover:bg-paper-100 lg:hidden" @click="nav = true" aria-label="Open menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
            </button>

            <h1 class="min-w-0 flex-1 truncate font-display text-lg font-bold text-navy-700">{{ $title }}</h1>

            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="flex items-center gap-2.5 rounded-xs px-2 py-1.5 hover:bg-paper-100">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-navy-700 font-display text-sm font-bold text-lime-400">{{ \Illuminate\Support\Str::of($user->name)->substr(0, 1)->upper() }}</span>
                    <span class="hidden text-left sm:block">
                        <span class="block font-display text-sm font-semibold leading-tight text-navy-700">{{ $user->name }}</span>
                        <span class="block text-xs leading-tight text-paper-500">{{ $user->getRoleNames()->first() }}</span>
                    </span>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute right-0 top-full mt-2 w-48 rounded-sm border border-paper-300 bg-white p-1.5 shadow-[var(--shadow-overlay)]">
                    <a href="{{ route('admin.account.edit') }}" class="block rounded-xs px-3 py-2 text-small text-navy-700 hover:bg-paper-100">My account</a>
                    <form method="POST" action="{{ route('admin.lock.now') }}">
                        @csrf
                        <button class="block w-full rounded-xs px-3 py-2 text-left text-small text-navy-700 hover:bg-paper-100">Lock screen</button>
                    </form>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="block w-full rounded-xs px-3 py-2 text-left text-small text-navy-700 hover:bg-paper-100">Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="px-4 py-8 sm:px-8">
            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 rounded-sm border border-lime-500/40 bg-lime-50 px-4 py-3 text-small text-navy-800" role="status" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition>
                    <x-icons.wb name="check" class="mt-0.5 h-4 w-4 text-lime-700" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-sm border border-status-crit/30 bg-status-crit/5 px-4 py-3 text-small text-status-crit" role="alert">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($actions)
                <div class="mb-6 flex flex-wrap items-center justify-end gap-3">{{ $actions }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <x-admin.session-guard />

    @livewireScripts
</body>
</html>
