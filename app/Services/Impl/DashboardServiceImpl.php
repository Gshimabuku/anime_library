<?php

namespace App\Services\Impl;

use App\Models\AnimeTitle;
use App\Models\Member;
use App\Models\Platform;
use App\Services\DashboardService;

class DashboardServiceImpl implements DashboardService
{
    /**
     * {@inheritdoc}
     */
    public function getStatistics(): array
    {
        return [
            'memberCount' => Member::count(),
            'titleCount' => AnimeTitle::count(),
            'platformCount' => Platform::count(),
        ];
    }
}
