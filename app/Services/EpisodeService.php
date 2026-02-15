<?php

namespace App\Services;

use App\Models\Series;
use Illuminate\Http\UploadedFile;

interface EpisodeService
{
    /**
     * CSVファイルからエピソードをインポートする
     *
     * @param Series $series 対象シリーズ
     * @param UploadedFile $csvFile CSVファイル
     * @return int インポートされたエピソード数
     */
    public function importFromCsv(Series $series, UploadedFile $csvFile): int;
}
