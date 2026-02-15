<?php

namespace App\Utils;

use App\Enums\SystemEnum;
use App\Models\System;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Cloudinary画像アップロードユーティリティ
 */
class CloudinaryUtil
{
    /**
     * 画像をCloudinaryにアップロードする
     *
     * @param UploadedFile|null $file アップロードファイル
     * @return string|null アップロード後の画像URL（ファイルがnullの場合はnull）
     * @throws ValidationException 設定不足やアップロード失敗時
     */
    public static function uploadImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $cloudName = System::getString(SystemEnum::CLOUDINARY_CLOUD_NAME);
        $apiKey = System::getString(SystemEnum::CLOUDINARY_API_KEY);
        $apiSecret = System::getString(SystemEnum::CLOUDINARY_API_SECRET);
        $folder = System::getString(SystemEnum::CLOUDINARY_FOLDER);

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw ValidationException::withMessages([
                'image' => 'Cloudinary設定が不足しています。systemsテーブルにcloudinary_cloud_name/cloudinary_api_key/cloudinary_api_secretを登録してください。',
            ]);
        }

        $timestamp = time();
        $signatureParams = [
            'timestamp' => $timestamp,
        ];

        if ($folder !== null) {
            $folder = trim($folder, '/');
            if ($folder !== '') {
                $signatureParams['folder'] = $folder;
            }
        }

        ksort($signatureParams);
        $signatureBase = http_build_query($signatureParams, '', '&', PHP_QUERY_RFC3986);
        $signature = sha1($signatureBase . $apiSecret);
        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        $payload = [
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];

        if (isset($signatureParams['folder'])) {
            $payload['folder'] = $signatureParams['folder'];
        }

        $response = Http::timeout(30)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($endpoint, $payload);

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'image' => 'Cloudinaryへのアップロードに失敗しました。' . ($response->json('error.message') ? ' ' . $response->json('error.message') : ''),
            ]);
        }

        $imageUrl = $response->json('secure_url');
        if (!$imageUrl) {
            throw ValidationException::withMessages([
                'image' => 'Cloudinaryから画像URLを取得できませんでした。',
            ]);
        }

        return $imageUrl;
    }
}
