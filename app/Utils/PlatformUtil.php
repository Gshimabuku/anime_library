<?php

namespace App\Utils;

use App\Models\AnimeTitle;
use App\Models\Platform;

/**
 * Platform関連の表示・計算ユーティリティ
 */
class PlatformUtil
{
    /**
     * プラットフォームに紐づく作品数を取得する
     */
    public static function getAnimeTitleCount(Platform $platform): int
    {
        return AnimeTitle::whereHas('series.seriesPlatformAvailabilities', function ($q) use ($platform) {
            $q->where('platform_id', $platform->id);
        })->count();
    }

    /**
     * プラットフォーム名からアイコンファイル名を取得する
     */
    public static function getIconFile(string $name): ?string
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
        return $map[$name] ?? null;
    }
}
