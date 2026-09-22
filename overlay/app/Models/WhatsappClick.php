<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['product_id', 'page'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
