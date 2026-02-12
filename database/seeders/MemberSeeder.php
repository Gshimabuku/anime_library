<?php

namespace Database\Seeders;

/**
 * メンバーテーブル
 * 　php artisan db:seed --class=MemberSeeder
 */
class MemberSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '00_SeederMaster.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'members';
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
