<?php

namespace App\Http\Requests\AnimeTitle;

use Illuminate\Foundation\Http\FormRequest;

class IndexAnimeTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'work_types' => 'nullable|array',
            'work_types.*' => 'integer',
        ];
    }

    /**
     * 検索キーワードを取得する
     */
    public function getKeyword(): ?string
    {
        return $this->input('keyword');
    }

    /**
     * 作品タイプフィルタを取得する
     */
    public function getWorkTypes(): array
    {
        return $this->input('work_types', \App\Enums\WorkType::values());
    }
}
