<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Services\Media\ImageUploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A catalogue item. Nothing is sold online: the product page shows the item
 * and a WhatsApp button, and the conversation happens there.
 */
class Product extends Model
{
    use Prunable, SoftDeletes;

    /** Days a deleted product stays restorable before it is removed for good. */
    public const RESTORE_DAYS = 30;

    public const STOCK_STATUSES = [
        'in_stock' => 'In stock',
        'on_order' => 'Available to order',
        'out_of_stock' => 'Out of stock',
    ];

    protected static function booted(): void
    {
        // The sitemap lists this model, so any change refreshes it.
        static::saved(fn () => Cache::forget('seo.sitemap'));
        static::deleted(fn () => Cache::forget('seo.sitemap'));
        static::restored(fn () => Cache::forget('seo.sitemap'));
    }

    /** Removed for good (with its photo) 30 days after being deleted. */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where('deleted_at', '<', now()->subDays(self::RESTORE_DAYS));
    }

    protected function pruning(): void
    {
        app(ImageUploader::class)->delete($this->image_path);
    }

    protected $fillable = [
        'product_category_id', 'name', 'slug', 'sku', 'brand', 'short_description', 'description',
        'price', 'currency', 'stock_status', 'specs', 'image_path',
        'is_featured', 'is_published', 'sort_order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'specs' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function whatsappClicks(): HasMany
    {
        return $this->hasMany(WhatsappClick::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order')->latest('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('uploads/'.$this->image_path) : null;
    }

    public function stockLabel(): string
    {
        return self::STOCK_STATUSES[$this->stock_status] ?? 'In stock';
    }

    public function hasPrice(): bool
    {
        return $this->price !== null && (float) $this->price > 0;
    }

    public function formattedPrice(): ?string
    {
        return $this->hasPrice() ? $this->currency.' '.number_format((float) $this->price, 2) : null;
    }

    /** The text the visitor's WhatsApp opens with. */
    public function whatsappMessage(): string
    {
        return collect([
            'Hello WestBridge, I am interested in: '.$this->name,
            $this->sku ? 'SKU: '.$this->sku : null,
            $this->formattedPrice() ? 'Listed price: '.$this->formattedPrice() : null,
            route('shop.product', $this->slug),
        ])->filter()->join("\n");
    }
}
