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
     * 全シリーズが配信されている作品のみカウントする
     */
    public static function getAnimeTitleCount(Platform $platform): int
    {
        return AnimeTitle::where(function ($query) use ($platform) {
            $query->whereRaw(
                '(SELECT COUNT(DISTINCT s.id) FROM series s WHERE s.anime_title_id = anime_titles.id) = '
                . '(SELECT COUNT(DISTINCT spa.series_id) FROM series_platform_availabilities spa '
                . 'INNER JOIN series s2 ON s2.id = spa.series_id '
                . 'WHERE s2.anime_title_id = anime_titles.id AND spa.platform_id = ?)',
                [$platform->id]
            );
        })
        ->whereHas('series') // シリーズが0件の作品を除外
        ->count();
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
