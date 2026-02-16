<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Series extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'anime_title_id',
        'name',
        'series_order',
        'format_type',
    ];

    public function animeTitle(): BelongsTo
    {
        return $this->belongsTo(AnimeTitle::class, 'anime_title_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('sort_order');
    }

    public function arcs(): HasMany
    {
        return $this->hasMany(Arc::class)->orderBy('start_episode_no');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'series_platform_availabilities')
                     ->withPivot('watch_condition')
                     ->withTimestamps();
    }

    public function seriesPlatformAvailabilities(): HasMany
    {
        return $this->hasMany(SeriesPlatformAvailability::class);
    }

    public function memberStatuses(): HasMany
    {
        return $this->hasMany(MemberSeriesStatus::class);
    }
}
