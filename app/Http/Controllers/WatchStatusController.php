<?php

namespace App\Http\Controllers;

use App\Models\AnimeTitle;
use App\Models\Member;
use App\Models\MemberSeriesStatus;
use Illuminate\Http\Request;

class WatchStatusController extends Controller
{
    public function index(Request $request)
    {
        // アクティブなメンバー一覧を取得
        $members = Member::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 全作品を取得（シリーズ情報も含む）
        $animeTitles = AnimeTitle::with(['series' => function ($query) {
            $query->orderBy('series_order');
        }])
        ->orderBy('id')
        ->get();

        // 全シリーズIDを取得
        $allSeriesIds = [];
        foreach ($animeTitles as $animeTitle) {
            foreach ($animeTitle->series as $series) {
                $allSeriesIds[] = $series->id;
            }
        }

        // 全ての視聴状況を取得
        $allWatchStatuses = MemberSeriesStatus::whereIn('series_id', $allSeriesIds)
            ->get()
            ->groupBy('series_id');

        return view('watch-status.index', compact('animeTitles', 'members', 'allWatchStatuses'));
    }
}
