<?php

namespace App\Http\Requests\AnimeTitle;

use App\Enums\SeriesFormatType;
use App\Enums\WatchCondition;
use App\Enums\WorkType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimeTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 作品基本情報
            'title' => 'required|string|max:255',
            'title_kana' => 'nullable|string|max:255',
            'work_type' => 'required|in:' . implode(',', WorkType::values()),
            'image' => 'nullable|image|max:5120',
            'note' => 'nullable|string',

            // シリーズ
            'series' => 'nullable|array',
            'series.*.id' => 'nullable|integer|exists:series,id',
            'series.*.name' => 'required|string|max:255',
            'series.*.format_type' => 'required|in:' . implode(',', SeriesFormatType::values()),
            'series.*.series_order' => 'required|integer|min:1',

            // シリーズ毎の配信PF
            'series.*.platforms' => 'nullable|array',
            'series.*.platforms.*.platform_id' => 'required|exists:platforms,id',
            'series.*.platforms.*.watch_condition' => 'required|in:' . implode(',', WatchCondition::values()),

            // エピソード
            'series.*.episodes' => 'nullable|array',
            'series.*.episodes.*.id' => 'nullable|integer|exists:episodes,id',
            'series.*.episodes.*.episode_no' => 'required|integer|min:1',
            'series.*.episodes.*.episode_title' => 'nullable|string|max:255',
            'series.*.episodes.*.onair_date' => 'nullable|integer',
            'series.*.episodes.*.duration_min' => 'required|integer|min:1',

            // アーク
            'series.*.arcs' => 'nullable|array',
            'series.*.arcs.*.id' => 'nullable|integer|exists:arcs,id',
            'series.*.arcs.*.name' => 'required|string|max:255',
            'series.*.arcs.*.start_episode_no' => 'required|integer|min:1',
            'series.*.arcs.*.end_episode_no' => 'required|integer|min:1',

            // 削除対象ID
            'deleted_series_ids' => 'nullable|array',
            'deleted_series_ids.*' => 'integer',
            'deleted_episode_ids' => 'nullable|array',
            'deleted_episode_ids.*' => 'integer',
            'deleted_arc_ids' => 'nullable|array',
            'deleted_arc_ids.*' => 'integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => '作品名',
            'title_kana' => '作品名（かな）',
            'work_type' => '作品タイプ',
            'image' => '作品画像',
            'note' => '備考',
            'series.*.name' => 'シリーズ名',
            'series.*.format_type' => 'フォーマット種別',
            'series.*.series_order' => 'シリーズ順序',
            'series.*.platforms.*.platform_id' => '配信プラットフォーム',
            'series.*.platforms.*.watch_condition' => '視聴条件',
            'series.*.episodes.*.episode_no' => '話数',
            'series.*.episodes.*.episode_title' => 'サブタイトル',
            'series.*.episodes.*.onair_date' => '放送年',
            'series.*.episodes.*.duration_min' => '尺（分）',
            'series.*.arcs.*.name' => 'アーク名',
            'series.*.arcs.*.start_episode_no' => '開始話数',
            'series.*.arcs.*.end_episode_no' => '終了話数',
        ];
    }
}
