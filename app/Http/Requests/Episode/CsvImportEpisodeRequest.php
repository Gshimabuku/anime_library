<?php

namespace App\Http\Requests\Episode;

use Illuminate\Foundation\Http\FormRequest;

class CsvImportEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'csv_file' => 'CSVファイル',
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'CSVファイルを選択してください。',
            'csv_file.file' => '有効なファイルをアップロードしてください。',
            'csv_file.mimes' => 'CSVファイル（.csv）を選択してください。',
            'csv_file.max' => 'ファイルサイズは2MB以下にしてください。',
        ];
    }
}
