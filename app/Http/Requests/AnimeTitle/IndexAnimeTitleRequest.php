<?php

namespace App\Http\Requests\AnimeTitle;

use App\Enums\WorkType;
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
            'series_count_min' => 'nullable|integer|min:0',
            'series_count_max' => 'nullable|integer|min:0',
            'special_count_min' => 'nullable|integer|min:0',
            'special_count_max' => 'nullable|integer|min:0',
            'movie_count_min' => 'nullable|integer|min:0',
            'movie_count_max' => 'nullable|integer|min:0',
            'episode_count_min' => 'nullable|integer|min:0',
            'episode_count_max' => 'nullable|integer|min:0',
            'duration_min' => 'nullable|integer|min:0',
            'duration_max' => 'nullable|integer|min:0',
            'platform_ids' => 'nullable|array',
            'platform_ids.*' => 'integer|exists:platforms,id',
        ];
    }

    public function getSearchParams(): array
    {
        return [
            'keyword' => $this->input('keyword'),
            'work_types' => $this->input('work_types', WorkType::values()),
            'series_count_min' => $this->filled('series_count_min') ? (int) $this->input('series_count_min') : null,
            'series_count_max' => $this->filled('series_count_max') ? (int) $this->input('series_count_max') : null,
            'special_count_min' => $this->filled('special_count_min') ? (int) $this->input('special_count_min') : null,
            'special_count_max' => $this->filled('special_count_max') ? (int) $this->input('special_count_max') : null,
            'movie_count_min' => $this->filled('movie_count_min') ? (int) $this->input('movie_count_min') : null,
            'movie_count_max' => $this->filled('movie_count_max') ? (int) $this->input('movie_count_max') : null,
            'episode_count_min' => $this->filled('episode_count_min') ? (int) $this->input('episode_count_min') : null,
            'episode_count_max' => $this->filled('episode_count_max') ? (int) $this->input('episode_count_max') : null,
            'duration_min' => $this->filled('duration_min') ? (int) $this->input('duration_min') : null,
            'duration_max' => $this->filled('duration_max') ? (int) $this->input('duration_max') : null,
            'platform_ids' => $this->input('platform_ids', []),
        ];
    }
}
