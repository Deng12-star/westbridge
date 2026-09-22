<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use Prunable, SoftDeletes;

    public const RESTORE_DAYS = 30;

    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'is_read', 'replied_at', 'source_page', 'ip_address'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function lead(): MorphOne
    {
        return $this->morphOne(Lead::class, 'sourceable');
    }

    /** Removed for good 30 days after being deleted, with its lead. */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where('deleted_at', '<', now()->subDays(self::RESTORE_DAYS));
    }

    protected function pruning(): void
    {
        Lead::withTrashed()->where('sourceable_type', $this->getMorphClass())->where('sourceable_id', $this->id)->forceDelete();
    }
}
