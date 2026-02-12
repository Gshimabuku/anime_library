<?php

namespace Database\Seeders;

/**
 * アニメタイトルテーブル
 * 　php artisan db:seed --class=AnimeTitleSeeder
 */
class AnimeTitleSeeder extends BaseDatabaseSeeder
{
    /** ファイル名 */
    protected function getFileName()
    {
        return '01_SeederWork.xlsx';
    }

    /** シート名 */
    protected function getSheetName()
    {
        return 'anime_titles';
    }

    protected function getColumnNames()
    {
        $ret = [
            'id',
            'title',
            'title_kana',
            'work_type',
            'note',
        ];
        return $ret;
    }
}
