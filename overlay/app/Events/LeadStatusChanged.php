<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly LeadStatus $from,
        public readonly LeadStatus $to,
    ) {}
}
