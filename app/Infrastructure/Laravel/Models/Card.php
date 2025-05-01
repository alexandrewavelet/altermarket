<?php

namespace App\Infrastructure\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $identifier
 * @property string $name
 * @property int $main_cost
 * @property int $recall_cost
 * @property int $mountain_power
 * @property int $ocean_power
 * @property int $forest_power
 * @property ?string $main_effect
 */
class Card extends Model
{
    public function card(): HasOne
    {
        return $this->hasOne(Offer::class);
    }
}
