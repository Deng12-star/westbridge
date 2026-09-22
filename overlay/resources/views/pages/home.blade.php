{{--
    Homepage, laid out on the QT Global pattern: immersive dark hero, services,
    a dark solutions showcase, a technology strip, about with counters, and a
    dark sector grid - in WestBridge navy and lime, with WestBridge's own words.
    The call-to-action band and footer come from the layout.
--}}
<x-layouts.app :title="null" :overlay-header="true">

    <x-home.hero />

    <x-home.services-grid />

    <x-home.solutions-showcase />

    <x-home.featured-products :products="$featuredProducts" />

    <x-home.tech-strip />

    <x-home.about />

    {{-- Our Projects hides itself until three case studies exist (config/portfolio.php). --}}
    <x-home.selected-work :projects="$projects" />
    <x-home.industries />

    {{-- Latest news hides itself until three posts are published. --}}
    <x-home.latest-news :posts="$latestPosts" />

</x-layouts.app>
