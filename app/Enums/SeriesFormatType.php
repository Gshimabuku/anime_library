<?php

namespace App\Enums;

enum SeriesFormatType: int
{
    case SERIES = 1;    // 第1期、シーズン1など
    case SPECIAL = 2;   // スペシャル
    case MOVIE  = 3;    // 映画要素

    public function label(): string
    {
        return match ($this) {
            self::SERIES => 'シリーズ',
            self::SPECIAL => 'スペシャル',
            self::MOVIE  => '映画',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
