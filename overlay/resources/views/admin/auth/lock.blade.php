<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Locked · Admin · {{ setting('company.name', 'WestBridge Technologies') }}</title>
    <link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css'])
    <script>window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });</script>
</head>
<body class="min-h-screen bg-navy-900 antialiased">
    <div class="pointer-events-none fixed -right-40 -top-40 h-[520px] w-[520px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <x-brand.logo variant="reverse" class="h-14" />

        <div class="mt-10 w-full max-w-sm rounded-sm bg-white p-8 text-center shadow-[0_30px_60px_-20px_rgba(0,0,0,0.6)]">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-navy-700 font-display text-xl font-bold text-lime-400">
                {{ \Illuminate\Support\Str::of($user->name)->substr(0, 1)->upper() }}
            </span>
            <h1 class="mt-4 font-display text-xl font-bold text-navy-700">Screen locked</h1>
            <p class="mt-1 text-small text-paper-600">{{ $user->name }} · {{ $user->email }}</p>
            <p class="mt-3 text-xs text-paper-500">Locked after {{ intdiv(\App\Support\AdminSession::lockSeconds(), 60) }} minutes without activity. Enter your password to carry on where you left off.</p>

            <form method="POST" action="{{ route('admin.unlock') }}" class="mt-6 grid gap-4 text-left">
                @csrf
                <x-admin.input name="password" type="password" label="Password" :required="true" autocomplete="current-password" autofocus />
                <x-admin.button class="w-full py-3">Unlock</x-admin.button>
            </form>

            <form method="POST" action="{{ route('admin.logout') }}" class="mt-4">
                @csrf
                <button class="text-small font-semibold text-paper-600 underline hover:text-navy-700">Not you? Sign out</button>
            </form>
        </div>
    </div>
</body>
</html>
