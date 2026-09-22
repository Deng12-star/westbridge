<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BudgetRange;
use App\Enums\ProjectTimeline;
use App\Enums\ServiceInterest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class QuoteRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'service' => ServiceInterest::class,
            'budget_range' => BudgetRange::class,
            'timeline' => ProjectTimeline::class,
        ];
    }

    public function lead(): MorphOne
    {
        return $this->morphOne(Lead::class, 'sourceable');
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_path);
    }

    /**
     * Attachments are stored on the private disk and never served directly —
     * admin downloads them through a signed, authorised route (Phase 7).
     */
    public function attachmentSizeForHumans(): ?string
    {
        if (! $this->attachment_size) {
            return null;
        }

        return number_format($this->attachment_size / 1048576, 1).' MB';
    }
}
