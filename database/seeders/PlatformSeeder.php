<?php

namespace Database\Seeders;

/**
 * プラットフォームテーブル
 * 　php artisan db:seed --class=PlatformSeeder
 */
class PlatformSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '00_SeederMaster.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'platforms';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'name',
            'sort_order',
            'is_active',
        ];
        return $ret;
    }
}
