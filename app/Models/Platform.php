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
                     ->withPivot('watch_condition', 'note')
                     ->withTimestamps();
    }

    public function getAnimeTitleCountAttribute(): int
    {
        return AnimeTitle::whereHas('series.seriesPlatformAvailabilities', function ($q) {
            $q->where('platform_id', $this->id);
        })->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            return $query->where('name', 'like', "%{$keyword}%");
        }
        return $query;
    }

    /**
     * プラットフォームアイコンファイル名を取得
     */
    public function getIconFileAttribute(): ?string
    {
        $map = [
            'Netflix' => 'netflix.png',
            'Amazon Prime Video' => 'amazon_prime_video.png',
            'U-NEXT' => 'unext.png',
            'Hulu' => 'hulu.png',
            'Disney+' => 'disney_plus.png',
            'FOD' => 'fod.png',
            'TELASA' => 'telasa.png',
            'Lemino' => 'lemino.png',
        ];
        return $map[$this->name] ?? null;
    }
}
