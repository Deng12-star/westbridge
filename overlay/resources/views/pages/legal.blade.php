<x-layouts.app :title="$page->meta_title ?? $page->title" :description="$page->meta_description">

    <x-page.hero :title="$page->heading ?? $page->title" :lede="$page->lede" />

    <section class="bg-white">
        <div class="wb-container py-16 md:py-20">
            <div class="mx-auto max-w-3xl">
                @if ($page->block('body'))
                    <div class="grid gap-4 text-paper-700">
                        {!! $page->block('body') !!}
                    </div>

                    @if ($page->updated_at)
                        <p class="mt-10 border-t border-paper-200 pt-5 text-small text-paper-500">
                            Last updated {{ $page->updated_at->format('j F Y') }}
                        </p>
                    @endif
                @else
                    <x-ui.empty-state
                        title="This page has not been published yet."
                        message="The content is being prepared. Contact us in the meantime and we will answer directly."
                        action="Contact us"
                        :href="route('contact')"
                    />
                @endif
            </div>
        </div>
    </section>

</x-layouts.app>
