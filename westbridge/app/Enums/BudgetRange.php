<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Budget bands rather than a free-text figure: easier for the customer to
 * answer honestly, and enough for sales to qualify. Currency follows
 * config('westbridge.shop.currency') — pending decision D2.
 */
enum BudgetRange: string
{
    case Undecided = 'undecided';
    case Under1k = 'under_1k';
    case From1kTo5k = '1k_5k';
    case From5kTo15k = '5k_15k';
    case From15kTo50k = '15k_50k';
    case Over50k = 'over_50k';

    public function label(): string
    {
        $c = config('westbridge.shop.currency', 'USD');

        return match ($this) {
            self::Undecided => 'Not decided yet',
            self::Under1k => "Under 1,000 {$c}",
            self::From1kTo5k => "1,000 – 5,000 {$c}",
            self::From5kTo15k => "5,000 – 15,000 {$c}",
            self::From15kTo50k => "15,000 – 50,000 {$c}",
            self::Over50k => "Over 50,000 {$c}",
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c): array => [$c->value => $c->label()])
            ->all();
    }
}
