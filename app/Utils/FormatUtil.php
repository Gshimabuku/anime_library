<?php

namespace App\Utils;

/**
 * 全体で共通して使用するフォーマットユーティリティ
 */
class FormatUtil
{
    /**
     * IDをゼロパディングして表示用に整形する
     */
    public static function padId(int $id, int $length = 3, string $prefix = ''): string
    {
        return $prefix . str_pad((string) $id, $length, '0', STR_PAD_LEFT);
    }

    /**
     * 分数を「○時間○分」形式で表示する
     */
    public static function formatDuration(int $totalMin): string
    {
        $hours = intdiv($totalMin, 60);
        $mins = $totalMin % 60;
        return "{$hours}時間{$mins}分";
    }
}
