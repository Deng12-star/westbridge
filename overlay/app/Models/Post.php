<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * A news post or company update.
 *
 * Visible to the public only when it is published AND its publish date has
 * arrived - so a post can be written now and scheduled for later.
 */
class Post extends Model
{
    public const CATEGORIES = ['News', 'Company update', 'Announcement', 'Project', 'Guide'];

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body', 'body_html', 'cover_path',
        'author_id', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('seo.sitemap'));
        static::deleted(fn () => Cache::forget('seo.sitemap'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Published and due: what the public may see. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function isLive(): bool
    {
        return $this->is_published && $this->published_at !== null && $this->published_at->lte(now());
    }

    /** Draft, Scheduled or Published - for the admin list. */
    public function statusLabel(): string
    {
        return match (true) {
            ! $this->is_published => 'Draft',
            $this->published_at?->isFuture() => 'Scheduled',
            default => 'Published',
        };
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? asset('uploads/'.$this->cover_path) : null;
    }

    /** The card/meta summary: the excerpt, or the start of the text. */
    public function summary(int $length = 180): string
    {
        return $this->excerpt ?: str(strip_tags((string) $this->body_html))->squish()->limit($length)->toString();
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body_html)) / 200));
    }
}
