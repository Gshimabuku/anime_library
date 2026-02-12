<?php

namespace Database\Seeders;

/**
 * シリーズテーブル
 * 　php artisan db:seed --class=SeriesSeeder
 */
class SeriesSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '01_SeederWork.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'series';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'anime_title_id',
            'name',
            'series_order',
            'format_type',
        ];
        return $ret;
    }
}
