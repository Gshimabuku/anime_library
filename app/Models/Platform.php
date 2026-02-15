<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function seriesPlatformAvailabilities(): HasMany
    {
        return $this->hasMany(SeriesPlatformAvailability::class);
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_platform_availabilities')
                     ->withPivot('watch_condition')
                     ->withTimestamps();
    }
}
