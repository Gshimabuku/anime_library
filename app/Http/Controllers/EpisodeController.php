<?php

namespace App\Http\Controllers;

use App\Http\Requests\Episode\CsvImportEpisodeRequest;
use App\Models\Series;
use App\Services\EpisodeService;

class EpisodeController extends Controller
{
    public function __construct(
        private readonly EpisodeService $episodeService
    ) {}

    /**
     * CSVインポートフォーム表示
     */
    public function csvImportForm(Series $series)
    {
        // エピソードが0件の場合のみ利用可能
        if ($series->episodes()->count() > 0) {
            return redirect()->route('works.show', $series->anime_title_id)
                ->with('error', 'このシリーズにはすでにエピソードが登録されています。');
        }

        $series->load('animeTitle');

        return view('episodes.csv-import', compact('series'));
    }

    /**
     * CSVインポート実行
     */
    public function csvImport(CsvImportEpisodeRequest $request, Series $series)
    {
        // エピソードが0件の場合のみ利用可能
        if ($series->episodes()->count() > 0) {
            return redirect()->route('works.show', $series->anime_title_id)
                ->with('error', 'このシリーズにはすでにエピソードが登録されています。');
        }

        try {
            $count = $this->episodeService->importFromCsv($series, $request->file('csv_file'));

            return redirect()->route('works.show', $series->anime_title_id)
                ->with('success', "{$series->name} に {$count} 件のエピソードをインポートしました。");
        } catch (\Exception $e) {
            return redirect()->route('episodes.csv-import-form', $series)
                ->with('error', $e->getMessage());
        }
    }
}
