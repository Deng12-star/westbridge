<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The services dropdown on the quote form. Kept as an enum rather than free
 * text so leads can be reported on by service line from day one.
 */
enum ServiceInterest: string
{
    case SoftwareDevelopment = 'software_development';
    case MobileApp = 'mobile_app';
    case Website = 'website';
    case Erp = 'erp';
    case ItInfrastructure = 'it_infrastructure';
    case Wifi = 'wifi';
    case Starlink = 'starlink';
    case Networking = 'networking';
    case Hardware = 'hardware';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SoftwareDevelopment => 'Software Development',
            self::MobileApp => 'Mobile App',
            self::Website => 'Website',
            self::Erp => 'ERP System',
            self::ItInfrastructure => 'IT Infrastructure',
            self::Wifi => 'WiFi Installation',
            self::Starlink => 'Starlink',
            self::Networking => 'Networking',
            self::Hardware => 'Hardware & Equipment',
            self::Other => 'Something else',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c): array => [$c->value => $c->label()])
            ->all();
    }

    /**
     * Maps a service page to its dropdown value so "Get Starlink" arrives at
     * the form with the right service already chosen.
     */
    public static function fromSlug(?string $slug): ?self
    {
        return match ($slug) {
            'software-development', 'software' => self::SoftwareDevelopment,
            'it-networking', 'networking' => self::Networking,
            'wifi' => self::Wifi,
            'starlink' => self::Starlink,
            'erp' => self::Erp,
            'website' => self::Website,
            'mobile-app' => self::MobileApp,
            'hardware', 'shop' => self::Hardware,
            default => null,
        };
    }
}
