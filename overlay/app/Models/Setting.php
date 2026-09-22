<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Every value that changes without a deployment lives here.
 * Nothing in a Blade template hard-codes a phone number, an address or a rate.
 */
class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label', 'is_public'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'is_public' => 'boolean',
        ];
    }
}
