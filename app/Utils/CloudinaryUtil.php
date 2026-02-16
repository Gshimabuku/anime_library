<?php

namespace App\Utils;

use App\Enums\SystemEnum;
use App\Models\System;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Cloudinary画像アップロード・削除ユーティリティ
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

    /**
     * Cloudinary上の画像URLからpublic_idを抽出する
     *
     * @param string $imageUrl Cloudinary画像URL
     * @return string|null public_id（抽出できない場合はnull）
     */
    public static function extractPublicId(string $imageUrl): ?string
    {
        // URL例: https://res.cloudinary.com/{cloud_name}/image/upload/v1234567890/folder/filename.jpg
        if (!preg_match('#/image/upload/(?:v\d+/)?(.+)\.[a-zA-Z]+$#', $imageUrl, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Cloudinary上の画像を削除する
     *
     * @param string|null $imageUrl 削除する画像のURL
     * @return bool 削除成功ならtrue
     */
    public static function deleteImage(?string $imageUrl): bool
    {
        if (!$imageUrl) {
            return false;
        }

        $publicId = self::extractPublicId($imageUrl);
        if (!$publicId) {
            Log::warning('Cloudinary画像のpublic_idを抽出できませんでした。', ['url' => $imageUrl]);
            return false;
        }

        $cloudName = System::getString(SystemEnum::CLOUDINARY_CLOUD_NAME);
        $apiKey = System::getString(SystemEnum::CLOUDINARY_API_KEY);
        $apiSecret = System::getString(SystemEnum::CLOUDINARY_API_SECRET);

        if (!$cloudName || !$apiKey || !$apiSecret) {
            Log::warning('Cloudinary設定が不足しているため、画像を削除できませんでした。');
            return false;
        }

        $timestamp = time();
        $signatureBase = "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}";
        $signature = sha1($signatureBase);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

        $response = Http::timeout(30)->post($endpoint, [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        if (!$response->successful() || $response->json('result') !== 'ok') {
            Log::warning('Cloudinary画像の削除に失敗しました。', [
                'url' => $imageUrl,
                'public_id' => $publicId,
                'response' => $response->json(),
            ]);
            return false;
        }

        return true;
    }
}
