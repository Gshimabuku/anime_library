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
    ];

    protected function casts(): array
    {
        return [
            'watch_condition' => WatchCondition::class,
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
