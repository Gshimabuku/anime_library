<?php

namespace Database\Seeders;

/**
 * メンバーシリーズ状態テーブル
 * 　php artisan db:seed --class=MemberSeriesStatusSeeder
 */
class MemberSeriesStatusSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '02_SeederRelationship.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'member_series_statuses';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'member_id',
            'series_id',
            'status',
        ];
        return $ret;
    }
}
