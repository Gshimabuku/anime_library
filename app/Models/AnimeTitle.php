<?php

namespace App\Models;

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
        'image_url',
    ];

    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'anime_title_id')->orderBy('series_order');
    }

    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, Series::class, 'anime_title_id', 'series_id');
    }
}
