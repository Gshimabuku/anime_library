<?php

namespace App\Http\Controllers;

use App\Enums\SeriesFormatType;
use App\Enums\WatchCondition;
use App\Enums\WorkType;
use App\Models\AnimeTitle;
use App\Models\Series;
use App\Models\Platform;
use App\Models\SeriesPlatformAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimeTitleController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $workTypes = $request->input('work_types', WorkType::values());

        $titles = AnimeTitle::search($keyword)
            ->ofWorkType($workTypes)
            ->orderBy('id')
            ->paginate(20);

        return view('works.index', compact('titles', 'keyword', 'workTypes'));
    }

    public function show(AnimeTitle $animeTitle)
    {
        $animeTitle->load([
            'series' => function ($q) {
                $q->orderBy('series_order');
            },
            'series.episodes' => function ($q) {
                $q->orderBy('episode_no');
            },
            'series.platforms' => function ($q) {
                $q->orderBy('sort_order');
            },
        ]);

        return view('works.show', compact('animeTitle'));
    }

    public function create()
    {
        $platforms = Platform::where('is_active', true)->orderBy('sort_order')->get();
        return view('works.form', [
            'animeTitle' => new AnimeTitle(),
            'platforms' => $platforms,
            'selectedPlatforms' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_kana' => 'nullable|string|max:255',
            'work_type' => 'required|in:' . implode(',', WorkType::values()),
            'note' => 'nullable|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'exists:platforms,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $animeTitle = AnimeTitle::create($validated);

            // デフォルトのシリーズを1つ作成
            $series = $animeTitle->series()->create([
                'name' => 'シーズン1',
                'series_order' => 1,
                'format_type' => $validated['work_type'] == WorkType::MOVIE_ONLY->value
                    ? SeriesFormatType::MOVIE->value
                    : SeriesFormatType::SERIES->value,
            ]);

            // プラットフォーム紐付け
            $platformIds = $request->input('platforms', []);
            foreach ($platformIds as $platformId) {
                SeriesPlatformAvailability::create([
                    'series_id' => $series->id,
                    'platform_id' => $platformId,
                    'watch_condition' => WatchCondition::SUBSCRIPTION->value,
                ]);
            }
        });

        return redirect()->route('works.index')
            ->with('success', '作品を追加しました。');
    }

    public function edit(AnimeTitle $animeTitle)
    {
        $platforms = Platform::where('is_active', true)->orderBy('sort_order')->get();

        // 紐付け済みプラットフォームIDを取得（全シリーズの合計）
        $selectedPlatforms = SeriesPlatformAvailability::whereIn(
            'series_id',
            $animeTitle->series()->pluck('id')
        )->pluck('platform_id')->unique()->toArray();

        return view('works.form', compact('animeTitle', 'platforms', 'selectedPlatforms'));
    }

    public function update(Request $request, AnimeTitle $animeTitle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_kana' => 'nullable|string|max:255',
            'work_type' => 'required|in:' . implode(',', WorkType::values()),
            'note' => 'nullable|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'exists:platforms,id',
        ]);

        DB::transaction(function () use ($validated, $request, $animeTitle) {
            $animeTitle->update($validated);

            // 全シリーズのプラットフォーム紐付けを更新
            $seriesIds = $animeTitle->series()->pluck('id');
            $platformIds = $request->input('platforms', []);

            foreach ($seriesIds as $seriesId) {
                SeriesPlatformAvailability::where('series_id', $seriesId)->delete();
                foreach ($platformIds as $platformId) {
                    SeriesPlatformAvailability::create([
                        'series_id' => $seriesId,
                        'platform_id' => $platformId,
                        'watch_condition' => WatchCondition::SUBSCRIPTION->value,
                    ]);
                }
            }
        });

        return redirect()->route('works.show', $animeTitle)
            ->with('success', '作品情報を更新しました。');
    }

    public function destroy(AnimeTitle $animeTitle)
    {
        $animeTitle->delete();

        return redirect()->route('works.index')
            ->with('success', '作品を削除しました。');
    }
}
