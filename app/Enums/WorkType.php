<?php

namespace App\Enums;

enum WorkType: int
{
    case SERIES_ONLY = 1;        // シリーズ作品
    case SERIES_PLUS_MOVIE = 2;   // シリーズ+映画作品
    case MOVIE_ONLY = 3;         // 映画のみ作品（単発）

    public function label(): string
    {
        return match ($this) {
            self::SERIES_ONLY => 'クール作品',
            self::SERIES_PLUS_MOVIE => 'クール+映画作品',
            self::MOVIE_ONLY => '映画のみ作品',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
