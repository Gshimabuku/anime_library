<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Seeder親クラス
 */
abstract class BaseDatabaseSeeder extends Seeder
{
    /** データファイル名 */
    abstract protected function getFileName();

    /** テーブル名取得（サブクラスで実装する） */
    abstract protected function getSheetName();

    /** カラム名取得（サブクラスで実装する） */
    abstract protected function getColumnNames();

    /**
     * Laravelから呼ばれるメソッド
     */
    public function run(): void
    {
        //------------------------------------------------------------------------------------------
        // テーブルの情報をサブクラスから取得
        //------------------------------------------------------------------------------------------
        // データファイル名
        $dataFileName = $this->getFileName();
        // テーブル名
        $sheetName = $this->getSheetName();
        // カラム名の配列
        $columnNames = $this->getColumnNames();

        //------------------------------------------------------------------------------------------
        // Excelファイル読み込み
        //------------------------------------------------------------------------------------------
        $spreadSheet = '';
        try {
            $path = database_path('seeders/' . $dataFileName);
            $spreadSheet = IOFactory::load($path);
        } catch (\Exception $e) {
            throw $e;
        }
        $spreadSheet->setActiveSheetIndexByName($sheetName);
        $workSheet = $spreadSheet->getActiveSheet();
        $strRange = $workSheet->calculateWorksheetDimension();
        $arrWS = $workSheet->rangeToArray($strRange);
        $arrHeader = array_shift($arrWS); // テーブル名
        $arrHeader = array_shift($arrWS); // カラム名

        //------------------------------------------------------------------------------------------
        // データ取得＆データ登録
        //------------------------------------------------------------------------------------------
        $list = [];
        $cnt = 0;
        foreach ($arrWS as $arrTmp) {
            $arrWSRow = array_combine($arrHeader, $arrTmp); // ヘッダーとデータを組み合わせて連想配列を作成
            $rowData = $this->createRowData($columnNames, $arrWSRow);
            if (!empty($rowData)) {
                $list = array_merge($list, array($rowData));
            }
            $cnt++;
            if ($cnt >= 2000) { // 10,000件登録でエラーとなったため、左記の単位で登録
                $this->insertList($sheetName, $list);
                $list = [];
                $cnt = 0;
            }
        }

        //------------------------------------------------------------------------------------------
        // データ登録
        //------------------------------------------------------------------------------------------
        if (!empty($list)) {
            try {
                $this->insertList($sheetName, $list);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Excelの行から連想配列を作成
     *
     * @param $columnNames 連想配列のkey
     * @param $arrWSRow    Excelの行データ
     *
     * @return 連想配列
     */
    protected function createRowData($columnNames, $arrWSRow)
    {
        $ret = [];
        $allNull = true; // 空のデータ範囲取得対応
        foreach ($columnNames as $col) {
            $ret[$col] = $arrWSRow[$col];
            if ($arrWSRow[$col]) {
                $allNull = false;
            }
        }
        // 全てnullの場合nullを返却
        if ($allNull) {
            return null;
        }
        return $ret;
    }

    /**
     * データ登録
     *
     * @param $tableName データを登録するテーブル名
     * @param $list 登録するデータ
     */
    protected function insertList($tableName, $list)
    {
        DB::table($tableName)->insert($list);
    }
}
