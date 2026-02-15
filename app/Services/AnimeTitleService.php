<?php

namespace App\Services;

use App\Models\AnimeTitle;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AnimeTitleService
{
    /**
     * 作品一覧を取得する（検索・フィルタ・ページネーション付き）
     */
    public function getAnimeTitles(array $searchParams): LengthAwarePaginator;

    /**
     * 作品詳細を取得する（リレーション込み）
     */
    public function getAnimeTitleDetail(AnimeTitle $animeTitle): AnimeTitle;

    /**
     * 有効な配信プラットフォーム一覧を取得する
     */
    public function getActivePlatforms(): Collection;

    /**
     * 作品に紐づくプラットフォームIDを取得する
     */
    public function getSelectedPlatformIds(AnimeTitle $animeTitle): array;

    /**
     * 作品を新規作成する（シリーズ・エピソード・アーク含む）
     */
    public function createAnimeTitle(array $data, ?UploadedFile $image): AnimeTitle;

    /**
     * 作品情報を更新する（シリーズ・エピソード・アーク含む）
     */
    public function updateAnimeTitle(AnimeTitle $animeTitle, array $data, ?UploadedFile $image): AnimeTitle;

    /**
     * 作品を削除する
     */
    public function deleteAnimeTitle(AnimeTitle $animeTitle): void;

    /**
     * CSVデータから作品を一括インポートする
     */
    public function importFromCsv(array $titlesData): int;

    /**
     * CSVデータからシリーズとエピソードを一括インポートする
     */
    public function importSeriesFromCsv(AnimeTitle $animeTitle, array $seriesData): int;
}
