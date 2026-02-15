<?php

namespace App\Http\Controllers;

use App\Enums\WatchStatus;
use App\Http\Requests\WatchStatus\IndexWatchStatusRequest;
use App\Models\AnimeTitle;
use App\Models\Member;
use App\Models\MemberSeriesStatus;

class WatchStatusController extends Controller
{
    public function index(IndexWatchStatusRequest $request)
    {
        $searchParams = $request->getSearchParams();
        $keyword = $searchParams['keyword'] ?? null;
        $watchStatusFilters = array_map('intval', $searchParams['watch_statuses'] ?? []);

        // アクティブなメンバー一覧を取得
        $members = Member::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 全作品を取得（シリーズ情報も含む）
        $query = AnimeTitle::with(['series' => function ($q) {
            $q->orderBy('series_order');
        }]);

        // 作品名フィルタ
        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%");
        }

        $animeTitles = $query->orderBy('id')->get();

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

        // 視聴状況でフィルタ
        if (!empty($watchStatusFilters)) {
            $animeTitles = $animeTitles->filter(function ($animeTitle) use ($members, $allWatchStatuses, $watchStatusFilters) {
                foreach ($members as $member) {
                    $watchedCount = 0;
                    $totalSeries = $animeTitle->series->count();

                    foreach ($animeTitle->series as $series) {
                        $status = null;
                        if (isset($allWatchStatuses[$series->id])) {
                            $status = $allWatchStatuses[$series->id]->firstWhere('member_id', $member->id);
                        }
                        if ($status && $status->status === WatchStatus::WATCHED->value) {
                            $watchedCount++;
                        }
                    }

                    // 集約ステータスを判定
                    $aggregateStatus = WatchStatus::UNWATCHED->value;
                    if ($watchedCount === $totalSeries && $totalSeries > 0) {
                        $aggregateStatus = WatchStatus::WATCHED->value;
                    } elseif ($watchedCount > 0) {
                        $aggregateStatus = WatchStatus::WATCHING->value;
                    }

                    if (in_array($aggregateStatus, $watchStatusFilters)) {
                        return true;
                    }
                }
                return false;
            });
        }

        return view('watch-status.index', compact('animeTitles', 'members', 'allWatchStatuses', 'searchParams'));
    }
}
