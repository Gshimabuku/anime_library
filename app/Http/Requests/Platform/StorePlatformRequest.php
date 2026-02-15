<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:platforms,name',
            'is_active' => 'required|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'プラットフォーム名',
            'is_active' => 'ステータス',
        ];
    }
}
