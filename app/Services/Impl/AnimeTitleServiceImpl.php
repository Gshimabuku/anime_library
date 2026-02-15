<?php

namespace App\Services\Impl;

use App\Enums\SeriesFormatType;
use App\Enums\WatchCondition;
use App\Enums\WorkType;
use App\Models\AnimeTitle;
use App\Models\Arc;
use App\Models\Episode;
use App\Models\Platform;
use App\Models\SeriesPlatformAvailability;
use App\Services\AnimeTitleService;
use App\Utils\CloudinaryUtil;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnimeTitleServiceImpl implements AnimeTitleService
{
    /**
     * {@inheritdoc}
     */
    public function getAnimeTitles(array $searchParams): LengthAwarePaginator
    {
        $keyword = $searchParams['keyword'] ?? null;
        $workTypes = $searchParams['work_types'] ?? null;
        $seriesCountMin = $searchParams['series_count_min'] ?? null;
        $seriesCountMax = $searchParams['series_count_max'] ?? null;
        $specialCountMin = $searchParams['special_count_min'] ?? null;
        $specialCountMax = $searchParams['special_count_max'] ?? null;
        $movieCountMin = $searchParams['movie_count_min'] ?? null;
        $movieCountMax = $searchParams['movie_count_max'] ?? null;
        $episodeCountMin = $searchParams['episode_count_min'] ?? null;
        $episodeCountMax = $searchParams['episode_count_max'] ?? null;
        $durationMin = $searchParams['duration_min'] ?? null;
        $durationMax = $searchParams['duration_max'] ?? null;
        $platformIds = $searchParams['platform_ids'] ?? [];

        return AnimeTitle::query()
            ->when($keyword, fn ($q) => $q->where('title', 'like', "%{$keyword}%"))
            ->when($workTypes && count($workTypes) > 0, fn ($q) => $q->whereIn('work_type', $workTypes))
            // シリーズ数フィルタ
            ->when($seriesCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) >= ?',
                [SeriesFormatType::SERIES->value, $seriesCountMin]
            ))
            ->when($seriesCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) <= ?',
                [SeriesFormatType::SERIES->value, $seriesCountMax]
            ))
            // スペシャル数フィルタ
            ->when($specialCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) >= ?',
                [SeriesFormatType::SPECIAL->value, $specialCountMin]
            ))
            ->when($specialCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) <= ?',
                [SeriesFormatType::SPECIAL->value, $specialCountMax]
            ))
            // 映画数フィルタ
            ->when($movieCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) >= ?',
                [SeriesFormatType::MOVIE->value, $movieCountMin]
            ))
            ->when($movieCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM series WHERE series.anime_title_id = anime_titles.id AND series.format_type = ?) <= ?',
                [SeriesFormatType::MOVIE->value, $movieCountMax]
            ))
            // 話数フィルタ（映画除外）
            ->when($episodeCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM episodes JOIN series ON episodes.series_id = series.id WHERE series.anime_title_id = anime_titles.id AND series.format_type != ?) >= ?',
                [SeriesFormatType::MOVIE->value, $episodeCountMin]
            ))
            ->when($episodeCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(*) FROM episodes JOIN series ON episodes.series_id = series.id WHERE series.anime_title_id = anime_titles.id AND series.format_type != ?) <= ?',
                [SeriesFormatType::MOVIE->value, $episodeCountMax]
            ))
            // 総視聴時間フィルタ（分）
            ->when($durationMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COALESCE(SUM(episodes.duration_min), 0) FROM episodes JOIN series ON episodes.series_id = series.id WHERE series.anime_title_id = anime_titles.id) >= ?',
                [$durationMin]
            ))
            ->when($durationMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COALESCE(SUM(episodes.duration_min), 0) FROM episodes JOIN series ON episodes.series_id = series.id WHERE series.anime_title_id = anime_titles.id) <= ?',
                [$durationMax]
            ))
            // 配信プラットフォームフィルタ
            ->when(!empty($platformIds), function ($q) use ($platformIds) {
                $q->whereExists(function ($subquery) use ($platformIds) {
                    $subquery->select(DB::raw(1))
                        ->from('series')
                        ->join('series_platform_availabilities', 'series.id', '=', 'series_platform_availabilities.series_id')
                        ->whereColumn('series.anime_title_id', 'anime_titles.id')
                        ->whereIn('series_platform_availabilities.platform_id', $platformIds);
                });
            })
            ->orderBy('id')
            ->paginate(50);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnimeTitleDetail(AnimeTitle $animeTitle): AnimeTitle
    {
        $animeTitle->load([
            'series' => function ($q) {
                $q->orderBy('series_order');
            },
            'series.episodes' => function ($q) {
                $q->orderBy('episode_no');
            },
            'series.platforms' => function ($q) {
                $q->orderBy('sort_order');
            },
        ]);

        return $animeTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePlatforms(): Collection
    {
        return Platform::where('is_active', true)->orderBy('sort_order')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectedPlatformIds(AnimeTitle $animeTitle): array
    {
        return SeriesPlatformAvailability::whereIn(
            'series_id',
            $animeTitle->series()->pluck('id')
        )->pluck('platform_id')->unique()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function createAnimeTitle(array $data, ?UploadedFile $image): AnimeTitle
    {
        $data['image_url'] = CloudinaryUtil::uploadImage($image);

        // シリーズのフォーマットタイプから作品タイプを自動決定
        $data['work_type'] = $this->determineWorkType($data['series'] ?? []);

        return DB::transaction(function () use ($data) {
            $animeTitle = AnimeTitle::create($data);

            // シリーズの作成
            $seriesDataList = $data['series'] ?? [];
            foreach ($seriesDataList as $seriesData) {
                $series = $animeTitle->series()->create([
                    'name' => $seriesData['name'],
                    'format_type' => $seriesData['format_type'],
                    'series_order' => $seriesData['series_order'],
                ]);

                // 配信PFの作成
                $this->syncSeriesPlatforms($series->id, $seriesData['platforms'] ?? []);

                // エピソードの作成
                $this->syncSeriesEpisodes($series->id, $seriesData['episodes'] ?? []);

                // アークの作成
                $this->syncSeriesArcs($series->id, $seriesData['arcs'] ?? []);
            }

            return $animeTitle;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function updateAnimeTitle(AnimeTitle $animeTitle, array $data, ?UploadedFile $image): AnimeTitle
    {
        $uploadedImageUrl = CloudinaryUtil::uploadImage($image);
        if ($uploadedImageUrl !== null) {
            $data['image_url'] = $uploadedImageUrl;
        }

        DB::transaction(function () use ($data, $animeTitle) {
            // 作品基本情報の更新
            $animeTitle->update([
                'title' => $data['title'],
                'title_kana' => $data['title_kana'] ?? null,
                'image_url' => $data['image_url'] ?? $animeTitle->image_url,
            ]);

            // 削除対象の処理
            $deletedSeriesIds = $data['deleted_series_ids'] ?? [];
            $deletedEpisodeIds = $data['deleted_episode_ids'] ?? [];
            $deletedArcIds = $data['deleted_arc_ids'] ?? [];

            if (!empty($deletedArcIds)) {
                Arc::whereIn('id', $deletedArcIds)->delete();
            }
            if (!empty($deletedEpisodeIds)) {
                Episode::whereIn('id', $deletedEpisodeIds)->delete();
            }
            if (!empty($deletedSeriesIds)) {
                \App\Models\Series::whereIn('id', $deletedSeriesIds)
                    ->where('anime_title_id', $animeTitle->id)
                    ->delete();
            }

            // シリーズの作成・更新
            $seriesDataList = $data['series'] ?? [];
            foreach ($seriesDataList as $seriesData) {
                $seriesId = $seriesData['id'] ?? null;

                if ($seriesId) {
                    // 既存シリーズの更新
                    $series = \App\Models\Series::find($seriesId);
                    if ($series && $series->anime_title_id === $animeTitle->id) {
                        $series->update([
                            'name' => $seriesData['name'],
                            'format_type' => $seriesData['format_type'],
                            'series_order' => $seriesData['series_order'],
                        ]);
                    }
                } else {
                    // 新規シリーズの作成
                    $series = $animeTitle->series()->create([
                        'name' => $seriesData['name'],
                        'format_type' => $seriesData['format_type'],
                        'series_order' => $seriesData['series_order'],
                    ]);
                    $seriesId = $series->id;
                }

                // 配信PFの同期
                $this->syncSeriesPlatforms($seriesId, $seriesData['platforms'] ?? []);

                // エピソードの作成・更新
                $this->syncSeriesEpisodes($seriesId, $seriesData['episodes'] ?? []);

                // アークの作成・更新
                $this->syncSeriesArcs($seriesId, $seriesData['arcs'] ?? []);
            }

            // シリーズのフォーマットタイプから作品タイプを自動再決定
            $animeTitle->refresh();
            $animeTitle->load('series');
            $seriesForUpdate = $animeTitle->series->map(fn($s) => ['format_type' => $s->format_type])->toArray();
            $animeTitle->update(['work_type' => $this->determineWorkType($seriesForUpdate)]);
        });

        return $animeTitle;
    }

    /**
     * シリーズの配信PF紐付けを同期する
     */
    private function syncSeriesPlatforms(int $seriesId, array $platformsData): void
    {
        SeriesPlatformAvailability::where('series_id', $seriesId)->delete();

        foreach ($platformsData as $pfData) {
            SeriesPlatformAvailability::create([
                'series_id' => $seriesId,
                'platform_id' => $pfData['platform_id'],
                'watch_condition' => $pfData['watch_condition'],
            ]);
        }
    }

    /**
     * シリーズのエピソードを同期する
     */
    private function syncSeriesEpisodes(int $seriesId, array $episodesData): void
    {
        foreach ($episodesData as $epData) {
            $episodeId = $epData['id'] ?? null;

            $attributes = [
                'series_id' => $seriesId,
                'episode_no' => $epData['episode_no'],
                'episode_title' => $epData['episode_title'] ?? null,
                'onair_date' => $epData['onair_date'] ?? null,
                'duration_min' => $epData['duration_min'],
            ];

            if ($episodeId) {
                $episode = Episode::find($episodeId);
                if ($episode && $episode->series_id === $seriesId) {
                    $episode->update($attributes);
                }
            } else {
                Episode::create($attributes);
            }
        }
    }

    /**
     * シリーズのアークを同期する
     */
    private function syncSeriesArcs(int $seriesId, array $arcsData): void
    {
        foreach ($arcsData as $arcData) {
            $arcId = $arcData['id'] ?? null;

            $attributes = [
                'series_id' => $seriesId,
                'name' => $arcData['name'],
                'start_episode_no' => $arcData['start_episode_no'],
                'end_episode_no' => $arcData['end_episode_no'],
            ];

            if ($arcId) {
                $arc = Arc::find($arcId);
                if ($arc && $arc->series_id === $seriesId) {
                    $arc->update($attributes);
                }
            } else {
                Arc::create($attributes);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    /**
     * シリーズのフォーマットタイプから作品タイプを自動決定する
     */
    private function determineWorkType(array $seriesDataList): int
    {
        if (empty($seriesDataList)) {
            return WorkType::SERIES_ONLY->value;
        }

        $hasMovie = false;
        $hasNonMovie = false;

        foreach ($seriesDataList as $seriesData) {
            $formatType = (int) ($seriesData['format_type'] ?? SeriesFormatType::SERIES->value);
            if ($formatType === SeriesFormatType::MOVIE->value) {
                $hasMovie = true;
            } else {
                $hasNonMovie = true;
            }
        }

        if ($hasMovie && $hasNonMovie) {
            return WorkType::SERIES_PLUS_MOVIE->value;
        }
        if ($hasMovie && !$hasNonMovie) {
            return WorkType::MOVIE_ONLY->value;
        }

        return WorkType::SERIES_ONLY->value;
    }

    public function deleteAnimeTitle(AnimeTitle $animeTitle): void
    {
        $animeTitle->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function importFromCsv(array $titlesData): int
    {
        return DB::transaction(function () use ($titlesData) {
            $count = 0;
            foreach ($titlesData as $titleData) {
                AnimeTitle::create([
                    'title' => $titleData['title'],
                    'title_kana' => $titleData['title_kana'] ?? null,
                    'work_type' => WorkType::SERIES_ONLY->value,
                ]);
                $count++;
            }
            return $count;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function importSeriesFromCsv(AnimeTitle $animeTitle, array $seriesDataList): int
    {
        return DB::transaction(function () use ($animeTitle, $seriesDataList) {
            $currentMaxOrder = $animeTitle->series()->max('series_order') ?? 0;
            $count = 0;

            foreach ($seriesDataList as $seriesData) {
                $currentMaxOrder++;
                $formatValue = $this->resolveFormatType($seriesData['format_type']);

                $series = $animeTitle->series()->create([
                    'name' => $seriesData['name'],
                    'format_type' => $formatValue,
                    'series_order' => $currentMaxOrder,
                ]);

                foreach ($seriesData['episodes'] as $index => $epData) {
                    Episode::create([
                        'series_id' => $series->id,
                        'episode_no' => $epData['episode_no'] ?? ($index + 1),
                        'episode_title' => $epData['episode_title'] ?? null,
                        'onair_date' => $epData['onair_date'] ?? null,
                        'duration_min' => $epData['duration_min'] ?? null,
                    ]);
                }

                $count++;
            }

            // 作品タイプを再決定
            $animeTitle->refresh();
            $animeTitle->load('series');
            $seriesForUpdate = $animeTitle->series->map(fn($s) => ['format_type' => $s->format_type])->toArray();
            $animeTitle->update(['work_type' => $this->determineWorkType($seriesForUpdate)]);

            return $count;
        });
    }

    /**
     * フォーマットタイプのラベルまたは値から enum 値を解決する
     */
    private function resolveFormatType(string $formatType): int
    {
        // 数値ならそのまま
        if (is_numeric($formatType)) {
            return (int) $formatType;
        }

        // ラベル名からマッチ
        foreach (SeriesFormatType::cases() as $case) {
            if ($case->label() === $formatType) {
                return $case->value;
            }
        }

        return SeriesFormatType::SERIES->value;
    }
}
