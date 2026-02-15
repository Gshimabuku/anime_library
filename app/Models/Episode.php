<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'episode_no',
        'episode_title',
        'onair_date',
        'duration_min',
    ];

    protected function casts(): array
    {
        return [
            'onair_date' => 'integer',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }
}
