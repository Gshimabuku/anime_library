<?php

namespace App\Enums;

enum WatchCondition: int
{
    case SUBSCRIPTION = 1;      // 見放題
    case POINT_PURCHASE = 2;     // ポイント購入
    case POINT_RENTAL = 3;       // ポイントレンタル

    public function label(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => '見放題',
            self::POINT_PURCHASE => 'ポイント購入',
            self::POINT_RENTAL => 'ポイントレンタル',
        };
    }

    public function isPointRequired(): bool
    {
        return in_array($this, [self::POINT_PURCHASE, self::POINT_RENTAL], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}
