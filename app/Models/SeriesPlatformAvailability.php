<?php

namespace App\Models;

use App\Enums\WatchCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesPlatformAvailability extends Model
{
    use HasFactory;

    protected $table = 'series_platform_availabilities';

    protected $fillable = [
        'series_id',
        'platform_id',
        'watch_condition',
        'note',
    ];

    const CONDITION_FREE = WatchCondition::SUBSCRIPTION->value;
    const CONDITION_POINT = WatchCondition::POINT_PURCHASE->value;
    const CONDITION_RENTAL = WatchCondition::POINT_RENTAL->value;
    const CONDITION_UNLIMITED = WatchCondition::SUBSCRIPTION->value;

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
