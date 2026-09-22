<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ServiceInterest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The CRM hub. Every enquiry from every entry point becomes one of these,
 * pointing back at whatever captured it.
 *
 * Business rules live in LeadService — this model holds relationships,
 * casts and scopes only.
 */
class Lead extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'service_interest' => ServiceInterest::class,
            'value_estimate' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [LeadStatus::Won->value, LeadStatus::Lost->value]);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /** Leads nobody has picked up within a day — surfaced amber on the dashboard. */
    public function scopeStale(Builder $query, int $hours = 24): Builder
    {
        return $query->open()->unassigned()->where('created_at', '<', now()->subHours($hours));
    }
}
