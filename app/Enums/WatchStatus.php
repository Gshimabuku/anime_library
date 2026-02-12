<?php

namespace App\Enums;

enum WatchStatus: int
{
    case UNWATCHED = 0; // 未視聴
    case WATCHING  = 1; // 視聴中
    case WATCHED   = 2; // 視聴済み

    public function label(): string
    {
        return match ($this) {
            self::UNWATCHED => '未視聴',
            self::WATCHING  => '視聴中',
            self::WATCHED   => '視聴済み',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
