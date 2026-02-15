<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名前',
            'is_active' => 'アクティブ',
        ];
    }
}
