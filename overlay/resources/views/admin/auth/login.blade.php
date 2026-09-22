<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Admin · {{ setting('company.name', 'WestBridge Technologies') }}</title>
    <link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css'])
    <script>window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });</script>
</head>
<body class="min-h-screen bg-navy-900 antialiased">
    <div class="pointer-events-none fixed -right-40 -top-40 h-[520px] w-[520px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <a href="{{ route('home') }}" aria-label="Back to the website">
            <x-brand.logo variant="reverse" class="h-16" />
        </a>

        <div class="mt-10 w-full max-w-md rounded-sm bg-white p-8 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.6)]">
            <h1 class="font-display text-2xl font-bold text-navy-700">Admin sign in</h1>
            <p class="mt-1 text-small text-paper-600">For WestBridge staff only.</p>

            @if (session('status'))
                <p class="mt-5 rounded-xs bg-lime-50 px-3 py-2 text-small text-navy-800">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 grid gap-5">
                @csrf
                <x-admin.input name="email" type="email" label="Email" :required="true" autocomplete="username" autofocus />
                <x-admin.input name="password" type="password" label="Password" :required="true" autocomplete="current-password" />


                <x-admin.button class="w-full py-3">Sign in</x-admin.button>
            </form>
        </div>

        <p class="mt-8 text-xs text-navy-300">Forgotten your password? Ask a Super Admin to reset it from Staff.</p>
    </div>
</body>
</html>
