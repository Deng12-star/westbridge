@php
    $siteName = setting('company.name', 'WestBridge Technologies');
    $metaTitle = trim($title ?? '') !== '' ? $title.' — '.$siteName : $siteName.' — '.setting('company.tagline', 'Connecting Ideas. Building Tomorrow.');
    $metaDescription = $description ?? setting('seo.default_description', 'WestBridge Technologies builds software, delivers IT infrastructure and connectivity, and supplies technology products in Juba, South Sudan.');
    $ogImage = $ogImage ?? asset('brand/og-default.jpg');
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ url()->current() }}">

{{-- Favicons generated from the official logo mark --}}
<link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('brand/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('brand/favicon-16x16.png') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#1C2B4F">

{{-- Open Graph / social --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="en_GB">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">

@if (setting('seo.google_site_verification'))
    <meta name="google-site-verification" content="{{ setting('seo.google_site_verification') }}">
@endif

@unless (app()->environment('production'))
    {{-- Only the live site should ever appear in search results. --}}
    <meta name="robots" content="noindex, nofollow">
@endunless

{{-- Organisation markup for Google - built in SeoService, not in Blade. --}}
<script type="application/ld+json">{!! json_encode(app(\App\Services\Seo\SeoService::class)->organizationSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

@if (app()->environment('production') && preg_match('/^G-[A-Z0-9]+$/', (string) setting('seo.google_analytics_id')))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('seo.google_analytics_id') }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ setting('seo.google_analytics_id') }}',{anonymize_ip:true});</script>
@endif
