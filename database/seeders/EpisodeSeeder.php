<?php

namespace Database\Seeders;

/**
 * エピソードテーブル
 * 　php artisan db:seed --class=EpisodeSeeder
 */
class EpisodeSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '01_SeederWork.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'episodes';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'series_id',
            'episode_no',
            'episode_title',
            'onair_date',
            'duration_min',
            'is_movie',
        ];
        return $ret;
    }
}
