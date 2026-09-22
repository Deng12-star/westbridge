<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case QuoteForm = 'quote_form';
    case ContactForm = 'contact_form';
    case ProductInquiry = 'product_inquiry';
    case ServiceInquiry = 'service_inquiry';
    case Order = 'order';
    case Manual = 'manual';
    case WhatsApp = 'whatsapp';
    case Referral = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::QuoteForm => 'Quote request',
            self::ContactForm => 'Contact form',
            self::ProductInquiry => 'Product enquiry',
            self::ServiceInquiry => 'Service enquiry',
            self::Order => 'Online order',
            self::Manual => 'Added by staff',
            self::WhatsApp => 'WhatsApp',
            self::Referral => 'Referral',
        };
    }
}
