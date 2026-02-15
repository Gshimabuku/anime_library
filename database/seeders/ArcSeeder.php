<?php

namespace Database\Seeders;

/**
 * アークテーブル
 * 　php artisan db:seed --class=ArcSeeder
 */
class ArcSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '01_SeederWork.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'arcs';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'series_id',
            'name',
            'start_episode_no',
            'end_episode_no',
        ];
        return $ret;
    }
}
