<?php

namespace Database\Seeders;

/**
 * シリーズ配信可用性テーブル
 * 　php artisan db:seed --class=SeriesPlatformAvailabilitySeeder
 */
class SeriesPlatformAvailabilitySeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '02_SeederRelationship.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'series_platform_availabilities';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'series_id',
            'platform_id',
            'watch_condition',
        ];
        return $ret;
    }
}
