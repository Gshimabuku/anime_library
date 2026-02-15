<?php

namespace App\Services\Impl;

use App\Models\Platform;
use App\Services\PlatformService;
use Illuminate\Pagination\LengthAwarePaginator;

class PlatformServiceImpl implements PlatformService
{
    /**
     * {@inheritdoc}
     */
    public function getPlatforms(array $searchParams): LengthAwarePaginator
    {
        $keyword = $searchParams['keyword'] ?? null;
        $titleCountMin = $searchParams['title_count_min'] ?? null;
        $titleCountMax = $searchParams['title_count_max'] ?? null;

        return Platform::query()
            ->where('is_active', true)
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->when($titleCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(DISTINCT s.anime_title_id) FROM series_platform_availabilities spa JOIN series s ON spa.series_id = s.id WHERE spa.platform_id = platforms.id) >= ?',
                [$titleCountMin]
            ))
            ->when($titleCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(DISTINCT s.anime_title_id) FROM series_platform_availabilities spa JOIN series s ON spa.series_id = s.id WHERE spa.platform_id = platforms.id) <= ?',
                [$titleCountMax]
            ))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(50);
    }

    /**
     * {@inheritdoc}
     */
    public function createPlatform(array $data): Platform
    {
        $maxOrder = Platform::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        return Platform::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePlatform(Platform $platform, array $data): Platform
    {
        $platform->update($data);
        return $platform;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePlatform(Platform $platform): bool
    {
        $platform->delete();
        return true;
    }
}
