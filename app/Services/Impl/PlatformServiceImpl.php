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
    public function getPlatforms(?string $keyword): LengthAwarePaginator
    {
        return Platform::query()
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);
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
