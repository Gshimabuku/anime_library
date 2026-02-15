<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class IndexMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'watched_count_min' => 'nullable|integer|min:0',
            'watched_count_max' => 'nullable|integer|min:0',
        ];
    }

    public function getSearchParams(): array
    {
        return [
            'keyword' => $this->input('keyword'),
            'watched_count_min' => $this->filled('watched_count_min') ? (int) $this->input('watched_count_min') : null,
            'watched_count_max' => $this->filled('watched_count_max') ? (int) $this->input('watched_count_max') : null,
        ];
    }
}
