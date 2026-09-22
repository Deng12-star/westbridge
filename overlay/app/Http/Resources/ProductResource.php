<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showPrice = (bool) (int) setting('shop.show_prices', '1') && $this->hasPrice();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'short_description' => $this->short_description,
            'description' => $this->when($request->routeIs('api.v1.products.show') || $request->user(), $this->description),
            'price' => $showPrice ? (float) $this->price : null,
            'currency' => $this->currency,
            'price_on_request' => ! $showPrice,
            'stock_status' => $this->stock_status,
            'stock_label' => $this->stockLabel(),
            'specs' => $this->specs ?? [],
            'image_url' => $this->image_url,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'url' => route('shop.product', $this->slug),
            'whatsapp_url' => whatsapp_url($this->whatsappMessage()),
            'is_published' => $this->when((bool) $request->user(), $this->is_published),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
