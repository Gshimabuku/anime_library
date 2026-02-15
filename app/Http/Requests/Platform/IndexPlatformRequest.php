<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class IndexPlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'title_count_min' => 'nullable|integer|min:0',
            'title_count_max' => 'nullable|integer|min:0',
        ];
    }

    public function getSearchParams(): array
    {
        return [
            'keyword' => $this->input('keyword'),
            'title_count_min' => $this->filled('title_count_min') ? (int) $this->input('title_count_min') : null,
            'title_count_max' => $this->filled('title_count_max') ? (int) $this->input('title_count_max') : null,
        ];
    }
}
