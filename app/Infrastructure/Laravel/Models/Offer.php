<?php

namespace App\Infrastructure\Laravel\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $identifier
 * @property ?int $price
 * @property bool $in_sale
 * @property ?Carbon $put_in_sale_at
 * @property ?Carbon $sold_at
 */
class Offer extends Model
{
    protected $casts = [
        'in_sale' => 'boolean',
        'put_in_sale_at' => 'immutable_datetime',
        'sold_at' => 'immutable_datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
