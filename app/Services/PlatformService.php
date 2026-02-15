<?php

namespace App\Services;

use App\Models\Platform;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlatformService
{
    /**
     * プラットフォーム一覧を取得する（検索・ページネーション付き）
     */
    public function getPlatforms(array $searchParams): LengthAwarePaginator;

    /**
     * プラットフォームを新規作成する
     */
    public function createPlatform(array $data): Platform;

    /**
     * プラットフォーム情報を更新する
     */
    public function updatePlatform(Platform $platform, array $data): Platform;

    /**
     * プラットフォームを削除する
     *
     * @return bool 削除成功時true
     * @throws \Exception 紐付きがある場合
     */
    public function deletePlatform(Platform $platform): bool;
}
