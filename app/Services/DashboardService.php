<?php

namespace App\Services;

interface DashboardService
{
    /**
     * ダッシュボード用の統計データを取得する
     *
     * @return array{memberCount: int, titleCount: int, platformCount: int}
     */
    public function getStatistics(): array;
}
