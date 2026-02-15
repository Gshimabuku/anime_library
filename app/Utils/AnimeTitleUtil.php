<?php

namespace App\Utils;

use App\Enums\SeriesFormatType;
use App\Enums\WatchCondition;
use App\Enums\WorkType;
use App\Models\AnimeTitle;
use App\Models\Episode;
use App\Models\Platform;
use App\Models\Series;
use App\Models\SeriesPlatformAvailability;

/**
 * AnimeTitle関連の表示・計算ユーティリティ
 */
class AnimeTitleUtil
{
    /**
     * 作品タイプのラベルを取得する
     */
    public static function getWorkTypeLabel(int $workType): string
    {
        return WorkType::tryFrom($workType)?->label() ?? '不明';
    }

    /**
     * シリーズ数を取得する（映画を除く）
     */
    public static function getSeriesCount(AnimeTitle $animeTitle): int
    {
        return $animeTitle->series()
            ->where('format_type', SeriesFormatType::SERIES->value)
            ->count();
    }

    /**
     * スペシャル数を取得する
     */
    public static function getSpecialCount(AnimeTitle $animeTitle): int
    {
        return $animeTitle->series()
            ->where('format_type', SeriesFormatType::SPECIAL->value)
            ->count();
    }

    /**
     * 映画数を取得する
     */
    public static function getMovieCount(AnimeTitle $animeTitle): int
    {
        return $animeTitle->series()
            ->where('format_type', SeriesFormatType::MOVIE->value)
            ->count();
    }

    /**
     * シリーズ数の表示文字列を取得する
     */
    public static function getSeriesCountDisplay(AnimeTitle $animeTitle): string
    {
        $seriesCount = self::getSeriesCount($animeTitle);
        $movieCount = self::getMovieCount($animeTitle);

        if ($animeTitle->work_type === WorkType::MOVIE_ONLY->value) {
            return "映画{$movieCount}";
        }
        if ($animeTitle->work_type === WorkType::SERIES_PLUS_MOVIE->value) {
            return "{$seriesCount} + 映画{$movieCount}";
        }
        return (string) $seriesCount;
    }

    /**
     * 総話数を取得する（映画を除く）
     */
    public static function getTotalEpisodes(AnimeTitle $animeTitle): int
    {
        return Episode::whereHas('series', function ($q) use ($animeTitle) {
            $q->where('anime_title_id', $animeTitle->id)
              ->where('format_type', '!=', SeriesFormatType::MOVIE->value);
        })->count();
    }

    /**
     * 総視聴時間（分）を取得する
     */
    public static function getTotalDurationMin(AnimeTitle $animeTitle): int
    {
        return Episode::whereHas('series', function ($q) use ($animeTitle) {
            $q->where('anime_title_id', $animeTitle->id);
        })->sum('duration_min');
    }

    /**
     * 総視聴時間の表示文字列を取得する
     */
    public static function getTotalDurationDisplay(AnimeTitle $animeTitle): string
    {
        $totalMin = self::getTotalDurationMin($animeTitle);
        $hours = intdiv($totalMin, 60);
        $mins = $totalMin % 60;
        return "{$hours}時間{$mins}分";
    }

    /**
     * 作品に紐づくプラットフォーム一覧を取得する
     * 全シリーズ（シリーズ、スペシャル、映画）が配信されているプラットフォームのみ返す
     */
    public static function getPlatforms(AnimeTitle $animeTitle)
    {
        $seriesIds = $animeTitle->series()->pluck('id');
        $seriesCount = $seriesIds->count();

        if ($seriesCount === 0) {
            return collect();
        }

        return Platform::select('platforms.*')
            ->join('series_platform_availabilities', 'platforms.id', '=', 'series_platform_availabilities.platform_id')
            ->whereIn('series_platform_availabilities.series_id', $seriesIds)
            ->groupBy('platforms.id', 'platforms.name', 'platforms.sort_order', 'platforms.is_active', 'platforms.created_at', 'platforms.updated_at')
            ->havingRaw('COUNT(DISTINCT series_platform_availabilities.series_id) = ?', [$seriesCount])
            ->orderBy('platforms.sort_order')
            ->get();
    }

    /**
     * ポイント必要なプラットフォームIDを取得する
     * 全シリーズ配信プラットフォームのうち、ポイント必要なもののみ返す
     */
    public static function getPointRequiredPlatformIds(AnimeTitle $animeTitle): array
    {
        $allSeriesPlatformIds = self::getPlatforms($animeTitle)->pluck('id');

        return SeriesPlatformAvailability::query()
            ->whereIn('series_id', $animeTitle->series()->select('id'))
            ->whereIn('platform_id', $allSeriesPlatformIds)
            ->whereIn('watch_condition', [
                WatchCondition::POINT_PURCHASE->value,
                WatchCondition::POINT_RENTAL->value,
            ])
            ->pluck('platform_id')
            ->unique()
            ->values()
            ->all();
    }
}
