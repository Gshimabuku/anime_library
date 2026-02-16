<?php

namespace App\Services\Impl;

use App\Models\Episode;
use App\Models\Series;
use App\Services\EpisodeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EpisodeServiceImpl implements EpisodeService
{
    private const ALLOWED_COLUMNS = ['episode_n', 'episode_title', 'onair_date', 'duration_min'];

    private const COLUMN_MAP = [
        'episode_n' => 'episode_no',
        'episode_title' => 'episode_title',
        'onair_date' => 'onair_date',
        'duration_min' => 'duration_min',
    ];

    public function importFromCsv(Series $series, UploadedFile $csvFile): int
    {
        $content = file_get_contents($csvFile->getRealPath());
        $content = $this->removeBom($content);
        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $content)), fn ($line) => trim($line) !== '');

        if (count($lines) < 2) {
            throw new \InvalidArgumentException('CSVファイルにはヘッダー行と少なくとも1行のデータが必要です。');
        }

        // ヘッダー行を解析
        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('trim', $headers);

        // ヘッダーのバリデーション
        $this->validateHeaders($headers);

        // データ行を解析
        $episodes = [];
        foreach ($lines as $lineNumber => $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($headers)) {
                $rowNumber = $lineNumber + 2; // ヘッダー行 + 0始まりのオフセット
                throw new \InvalidArgumentException("CSVの{$rowNumber}行目のカラム数がヘッダーと一致しません。");
            }

            $row = array_combine($headers, array_map('trim', $values));
            $episodeData = $this->buildEpisodeData($row, $lineNumber + 2);
            $episodes[] = $episodeData;
        }

        // episode_nが指定されている場合、重複チェック
        if (in_array('episode_n', $headers)) {
            $episodeNos = array_column($episodes, 'episode_no');
            if (count($episodeNos) !== count(array_unique($episodeNos))) {
                throw new \InvalidArgumentException('CSVファイル内にエピソード番号の重複があります。');
            }
        }

        // DB登録
        return DB::transaction(function () use ($series, $episodes, $headers) {
            $count = 0;
            foreach ($episodes as $index => $episodeData) {
                // episode_nが指定されていない場合、連番を自動付与
                if (!isset($episodeData['episode_no'])) {
                    $episodeData['episode_no'] = '第' . ($index + 1) . '話';
                }
                $episodeData['series_id'] = $series->id;
                $episodeData['sort_order'] = $index + 1;

                Episode::create($episodeData);
                $count++;
            }
            return $count;
        });
    }

    /**
     * BOMを除去する
     */
    private function removeBom(string $content): string
    {
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }
        return $content;
    }

    /**
     * ヘッダー行のバリデーション
     */
    private function validateHeaders(array $headers): void
    {
        if (empty($headers)) {
            throw new \InvalidArgumentException('CSVファイルのヘッダーが空です。');
        }

        foreach ($headers as $header) {
            if (!in_array($header, self::ALLOWED_COLUMNS)) {
                $allowed = implode(', ', self::ALLOWED_COLUMNS);
                throw new \InvalidArgumentException(
                    "不正なカラム名「{$header}」が指定されています。使用可能なカラム: {$allowed}"
                );
            }
        }

        // 重複チェック
        if (count($headers) !== count(array_unique($headers))) {
            throw new \InvalidArgumentException('CSVファイルのヘッダーに重複するカラム名があります。');
        }
    }

    /**
     * 行データからエピソードデータを組み立てる
     */
    private function buildEpisodeData(array $row, int $rowNumber): array
    {
        $data = [];

        if (isset($row['episode_n'])) {
            $value = $row['episode_n'];
            if (mb_strlen($value) === 0) {
                throw new \InvalidArgumentException(
                    "CSVの{$rowNumber}行目: episode_nは空にできません。"
                );
            }
            if (mb_strlen($value) > 20) {
                throw new \InvalidArgumentException(
                    "CSVの{$rowNumber}行目: episode_nは20文字以内で指定してください。（値: {$value}）"
                );
            }
            $data['episode_no'] = $value;
        }

        if (isset($row['episode_title'])) {
            $value = $row['episode_title'];
            if (mb_strlen($value) > 255) {
                throw new \InvalidArgumentException(
                    "CSVの{$rowNumber}行目: episode_titleは255文字以内で指定してください。"
                );
            }
            $data['episode_title'] = $value;
        }

        if (isset($row['duration_min'])) {
            $value = $row['duration_min'];
            if (!is_numeric($value) || (int) $value < 1) {
                throw new \InvalidArgumentException(
                    "CSVの{$rowNumber}行目: duration_minは1以上の整数を指定してください。（値: {$value}）"
                );
            }
            $data['duration_min'] = (int) $value;
        }

        if (isset($row['onair_date'])) {
            $value = $row['onair_date'];
            if ($value !== '' && (!is_numeric($value) || (int) $value < 1900 || (int) $value > 2100)) {
                throw new \InvalidArgumentException(
                    "CSVの{$rowNumber}行目: onair_dateは西暦年（1900〜2100）を指定してください。（値: {$value}）"
                );
            }
            $data['onair_date'] = $value !== '' ? (int) $value : null;
        }

        return $data;
    }
}
