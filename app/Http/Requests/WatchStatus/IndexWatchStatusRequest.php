<?php

namespace App\Http\Requests\WatchStatus;

use Illuminate\Foundation\Http\FormRequest;

class IndexWatchStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'watch_statuses' => 'nullable|array',
            'watch_statuses.*' => 'integer',
        ];
    }

    public function getSearchParams(): array
    {
        return [
            'keyword' => $this->input('keyword'),
            'watch_statuses' => $this->input('watch_statuses', []),
        ];
    }
}
