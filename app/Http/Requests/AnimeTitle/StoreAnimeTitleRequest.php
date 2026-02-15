<?php

namespace App\Http\Requests\AnimeTitle;

use App\Enums\WorkType;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnimeTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_kana' => 'nullable|string|max:255',
            'work_type' => 'required|in:' . implode(',', WorkType::values()),
            'image' => 'nullable|image|max:5120',
            'note' => 'nullable|string',
            'platforms' => 'nullable|array',
            'platforms.*' => 'exists:platforms,id',
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
            'platforms' => '配信プラットフォーム',
        ];
    }

    /**
     * プラットフォームIDの配列を取得する
     */
    public function getPlatformIds(): array
    {
        return $this->input('platforms', []);
    }
}
