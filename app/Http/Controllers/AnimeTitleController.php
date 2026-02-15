<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeTitle\IndexAnimeTitleRequest;
use App\Http\Requests\AnimeTitle\StoreAnimeTitleRequest;
use App\Http\Requests\AnimeTitle\UpdateAnimeTitleRequest;
use App\Models\AnimeTitle;
use App\Services\AnimeTitleService;
use Illuminate\Http\Request;

class AnimeTitleController extends Controller
{
    public function __construct(
        private readonly AnimeTitleService $animeTitleService
    ) {}

    public function index(IndexAnimeTitleRequest $request)
    {
        $searchParams = $request->getSearchParams();
        $titles = $this->animeTitleService->getAnimeTitles($searchParams);
        $platforms = $this->animeTitleService->getActivePlatforms();

        return view('works.index', compact('titles', 'searchParams', 'platforms'));
    }

    public function show(AnimeTitle $animeTitle)
    {
        $animeTitle = $this->animeTitleService->getAnimeTitleDetail($animeTitle);

        return view('works.show', compact('animeTitle'));
    }

    public function create()
    {
        $animeTitle = new AnimeTitle();
        $animeTitle->setRelation('series', collect());
        $platforms = $this->animeTitleService->getActivePlatforms();

        return view('works.form', compact('animeTitle', 'platforms'));
    }

    public function store(StoreAnimeTitleRequest $request)
    {
        $this->animeTitleService->createAnimeTitle(
            $request->validated(),
            $request->file('image'),
        );

        return redirect()->route('works.index')
            ->with('success', '作品を追加しました。');
    }

    public function edit(AnimeTitle $animeTitle)
    {
        $animeTitle = $this->animeTitleService->getAnimeTitleDetail($animeTitle);
        $animeTitle->load(['series.arcs', 'series.seriesPlatformAvailabilities']);
        $platforms = $this->animeTitleService->getActivePlatforms();

        return view('works.form', compact('animeTitle', 'platforms'));
    }

    public function update(UpdateAnimeTitleRequest $request, AnimeTitle $animeTitle)
    {
        $this->animeTitleService->updateAnimeTitle(
            $animeTitle,
            $request->validated(),
            $request->file('image'),
        );

        return redirect()->route('works.show', $animeTitle)
            ->with('success', '作品情報を更新しました。');
    }

    public function destroy(AnimeTitle $animeTitle)
    {
        $this->animeTitleService->deleteAnimeTitle($animeTitle);

        return redirect()->route('works.index')
            ->with('success', '作品を削除しました。');
    }

    public function csvImport(Request $request)
    {
        $request->validate([
            'titles' => 'required|array|min:1',
            'titles.*.title' => 'required|string|max:255',
            'titles.*.title_kana' => 'nullable|string|max:255',
        ]);

        try {
            $count = $this->animeTitleService->importFromCsv($request->input('titles'));
            return response()->json([
                'success' => true,
                'message' => "{$count} 件の作品をインポートしました。",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function seriesCsvImport(Request $request, AnimeTitle $animeTitle)
    {
        $request->validate([
            'series' => 'required|array|min:1',
            'series.*.name' => 'required|string|max:255',
            'series.*.format_type' => 'required|string',
            'series.*.episodes' => 'required|array|min:1',
        ]);

        try {
            $count = $this->animeTitleService->importSeriesFromCsv($animeTitle, $request->input('series'));
            return response()->json([
                'success' => true,
                'message' => "{$count} 件のシリーズをインポートしました。",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function watchStatus(AnimeTitle $animeTitle)
    {
        // 作品とシリーズ、エピソード情報を取得
        $animeTitle->load(['series' => function ($query) {
            $query->orderBy('series_order');
        }, 'series.episodes']);

        // アクティブなメンバー一覧を取得
        $members = \App\Models\Member::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 各シリーズの視聴状況を取得
        $seriesIds = $animeTitle->series->pluck('id')->toArray();
        $watchStatuses = \App\Models\MemberSeriesStatus::whereIn('series_id', $seriesIds)
            ->get()
            ->groupBy('series_id');

        return view('works.watch-status', compact('animeTitle', 'members', 'watchStatuses'));
    }
}
