<?php

namespace App\Models;

use App\Enums\WatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberSeriesStatus extends Model
{
    use HasFactory;

    protected $table = 'member_series_statuses';

    protected $fillable = [
        'member_id',
        'series_id',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    const STATUS_UNWATCHED = WatchStatus::UNWATCHED->value;
    const STATUS_WATCHING = WatchStatus::WATCHING->value;
    const STATUS_WATCHED = WatchStatus::WATCHED->value;

    const STATUS_LABELS = [
        self::STATUS_UNWATCHED => '未視聴',
        self::STATUS_WATCHING => '視聴中',
        self::STATUS_WATCHED => '視聴済',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return WatchStatus::tryFrom($this->status)?->label() ?? '不明';
    }
}
