<?php

namespace App\Models;

use App\Enums\SeriesFormatType;
use App\Enums\WorkType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AnimeTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_kana',
        'work_type',
        'note',
    ];

    const WORK_TYPE_COUR_ONLY = WorkType::SERIES_ONLY->value;
    const WORK_TYPE_COUR_PLUS_MOVIE = WorkType::SERIES_PLUS_MOVIE->value;
    const WORK_TYPE_MOVIE_ONLY = WorkType::MOVIE_ONLY->value;

    const WORK_TYPE_LABELS = [
        self::WORK_TYPE_COUR_ONLY => 'シリーズのみ',
        self::WORK_TYPE_COUR_PLUS_MOVIE => 'シリーズ+映画',
        self::WORK_TYPE_MOVIE_ONLY => '映画のみ',
    ];

    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'anime_title_id')->orderBy('series_order');
    }

    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, Series::class, 'anime_title_id', 'series_id');
    }

    public function getWorkTypeLabelAttribute(): string
    {
        return WorkType::tryFrom($this->work_type)?->label() ?? '不明';
    }

    public function getSeriesCountAttribute(): int
    {
        return $this->series()->where('format_type', SeriesFormatType::SERIES->value)->count();
    }

    public function getMovieCountAttribute(): int
    {
        return $this->series()->where('format_type', SeriesFormatType::MOVIE->value)->count();
    }

    public function getSeriesCountDisplayAttribute(): string
    {
        $seriesCount = $this->series_count;
        $movieCount = $this->movie_count;

        if ($this->work_type === self::WORK_TYPE_MOVIE_ONLY) {
            return "映画{$movieCount}";
        }
        if ($this->work_type === self::WORK_TYPE_COUR_PLUS_MOVIE) {
            return "{$seriesCount} + 映画{$movieCount}";
        }
        return (string) $seriesCount;
    }

    public function getTotalEpisodesAttribute(): int
    {
        return $this->episodes()->where('is_movie', false)->count();
    }

    public function getTotalDurationMinAttribute(): int
    {
        return $this->episodes()->sum('duration_min');
    }

    public function getTotalDurationDisplayAttribute(): string
    {
        $totalMin = $this->total_duration_min;
        $hours = intdiv($totalMin, 60);
        $mins = $totalMin % 60;
        return "{$hours}時間{$mins}分";
    }

    public function getPlatformsAttribute()
    {
        return Platform::whereHas('seriesPlatformAvailabilities', function ($q) {
            $q->whereIn('series_id', $this->series()->pluck('id'));
        })->orderBy('sort_order')->get()->unique('id');
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            return $query->where('title', 'like', "%{$keyword}%");
        }
        return $query;
    }

    public function scopeOfWorkType($query, ?array $types)
    {
        if ($types && count($types) > 0) {
            return $query->whereIn('work_type', $types);
        }
        return $query;
    }
}
