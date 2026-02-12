<?php

namespace App\Models;

use App\Enums\SeriesFormatType;
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

    const FORMAT_SERIES = SeriesFormatType::SERIES->value;
    const FORMAT_SPECIAL = SeriesFormatType::SPECIAL->value;
    const FORMAT_MOVIE = SeriesFormatType::MOVIE->value;

    public function animeTitle(): BelongsTo
    {
        return $this->belongsTo(AnimeTitle::class, 'anime_title_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('episode_no');
    }

    public function arcs(): HasMany
    {
        return $this->hasMany(Arc::class)->orderBy('arc_order');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'series_platform_availabilities')
                     ->withPivot('watch_condition', 'note')
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

    public function isMovie(): bool
    {
        return $this->format_type === self::FORMAT_MOVIE;
    }
}
