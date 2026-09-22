<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectTimeline: string
{
    case Immediately = 'immediately';
    case WithinMonth = 'within_month';
    case OneToThreeMonths = '1_3_months';
    case ThreeToSixMonths = '3_6_months';
    case Planning = 'planning';

    public function label(): string
    {
        return match ($this) {
            self::Immediately => 'As soon as possible',
            self::WithinMonth => 'Within a month',
            self::OneToThreeMonths => 'One to three months',
            self::ThreeToSixMonths => 'Three to six months',
            self::Planning => 'Still planning',
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
