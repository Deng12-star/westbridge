<x-layouts.app title="Your session expired">
    <section class="bg-paper-100">
        <div class="wb-container flex min-h-[60vh] flex-col items-center justify-center py-20 text-center">
            <p class="font-mono text-small text-lime-700">419</p>
            <h1 class="mt-3 text-h1">Your session expired.</h1>
            <p class="mt-4 max-w-md text-paper-600">
                The page was open for a while and the form is no longer valid. Go back and submit it again —
                your details were not sent.
            </p>
            <div class="mt-8"><a href="{{ url()->previous() }}"><x-ui.button>Go back and retry</x-ui.button></a></div>
        </div>
    </section>
</x-layouts.app>
