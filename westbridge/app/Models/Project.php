<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Project extends Model
{
    public const MOCKS = [
        'dashboard' => 'Dashboard',
        'payroll' => 'Payroll',
        'loans' => 'Loans',
        'shop' => 'Online shop',
        'school' => 'School / records',
        'network' => 'Network',
        'cctv' => 'CCTV cameras',
        'store' => 'Product grid',
    ];

    protected static function booted(): void
    {
        // The sitemap lists this model, so any change refreshes it.
        static::saved(fn () => Cache::forget('seo.sitemap'));
        static::deleted(fn () => Cache::forget('seo.sitemap'));
    }

    protected $fillable = [
        'title', 'slug', 'client', 'location', 'industry', 'category', 'service', 'status', 'year',
        'mock', 'live_url', 'show_live_link', 'summary', 'overview', 'scope', 'image_path',
        'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'overview' => 'array',
            'scope' => 'array',
            'show_live_link' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('uploads/'.$this->image_path) : null;
    }
}
