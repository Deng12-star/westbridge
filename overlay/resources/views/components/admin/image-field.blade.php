@props(['name' => 'image', 'label' => 'Image', 'current' => null, 'help' => 'JPG, PNG or WebP, up to 5 MB. Large photos are resized automatically.'])

<div x-data="{ preview: @js($current), removed: false }">
    <span class="block font-display text-sm font-semibold text-navy-700">{{ $label }}</span>

    <div class="mt-1.5 flex flex-col gap-4 sm:flex-row sm:items-start">
        <div class="flex aspect-square w-40 flex-shrink-0 items-center justify-center overflow-hidden rounded-sm border border-dashed border-paper-300 bg-paper-50">
            <template x-if="preview && !removed">
                <img :src="preview" alt="" class="h-full w-full object-contain">
            </template>
            <template x-if="!preview || removed">
                <x-icons.wb name="device" class="h-8 w-8 text-paper-400" />
            </template>
        </div>

        <div class="grid gap-2">
            <label class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-xs border border-paper-300 bg-white px-4 py-2.5 font-display text-sm font-semibold text-navy-700 hover:border-navy-400">
                Choose image
                <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="sr-only"
                    @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); removed = false }">
            </label>
            @if ($current)
                <label class="flex items-center gap-2 text-small text-paper-600">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" x-model="removed" class="rounded-xs border-paper-300"> Remove current image
                </label>
            @endif
            <p class="text-xs text-paper-500">{{ $help }}</p>
            @error($name)<p class="text-xs text-status-crit">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
