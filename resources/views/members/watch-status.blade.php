@extends('layouts.app')

@section('title', '視聴状況 - ' . $member->name . ' - アニメ管理システム')

@section('content')
    @php
        use App\Enums\WatchStatus;
        use App\Utils\AnimeTitleUtil;
        use App\Utils\PlatformUtil;
    @endphp

    <h1 class="page-title">{{ $member->name }} の視聴状況</h1>

    <div class="list-container">
        <div class="detail-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <a href="{{ route('members.show', $member) }}" class="btn btn-secondary">メンバー詳細に戻る</a>
                <a href="{{ route('members.index') }}" class="btn btn-secondary">メンバー一覧に戻る</a>
            </div>
        </div>

        {{-- 凡例 --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold; color: #6c757d;">-</span>
                <span>未視聴</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold; color: #007bff;">△</span>
                <span>一部視聴</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold; color: #28a745;">〇</span>
                <span>視聴済み</span>
            </div>
        </div>

        @if($animeTitles->isEmpty())
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px;">
                <p style="color: #6c757d; font-size: 1.1em; margin: 0;">作品が登録されていません。</p>
            </div>
        @else
            {{-- 作品リスト --}}
            @foreach($animeTitles as $animeTitle)
                @php
                    // この作品の全シリーズに対するメンバーの視聴状況を集計
                    $watchedCount = 0;
                    $totalSeries = $animeTitle->series->count();

                    foreach($animeTitle->series as $series) {
                        $status = isset($watchStatuses[$series->id]) ? $watchStatuses[$series->id]->first() : null;
                        if ($status && $status->status === WatchStatus::WATCHED->value) {
                            $watchedCount++;
                        }
                    }

                    // 表示する記号と色を決定
                    $symbol = '-';
                    $color = '#6c757d';
                    $statusLabel = '未視聴';

                    if ($watchedCount === $totalSeries && $totalSeries > 0) {
                        // すべてのシリーズが視聴済み
                        $symbol = '〇';
                        $color = '#28a745';
                        $statusLabel = '視聴済み';
                    } elseif ($watchedCount > 0) {
                        // 一部のシリーズが視聴済み
                        $symbol = '△';
                        $color = '#007bff';
                        $statusLabel = '一部視聴';
                    }

                    $imageUrl = $animeTitle->image_url;
                    if (!$imageUrl) {
                        $imagePath = 'images/works/works_' . str_pad($animeTitle->id, 3, '0', STR_PAD_LEFT) . '.jpg';
                        $imageUrl = file_exists(public_path($imagePath)) ? asset($imagePath) : null;
                    }
                @endphp

                <div class="work-status-card" style="margin-bottom: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    {{-- 作品ヘッダー（クリックで展開） --}}
                    <div class="work-header" onclick="toggleWorkDetail(this)" style="display: flex; gap: 15px; padding: 15px; cursor: pointer; align-items: center; transition: background-color 0.2s;">
                        {{-- 展開アイコン --}}
                        <div class="toggle-icon" style="font-size: 1.2em; color: #6c757d; transition: transform 0.3s;">
                            ▶
                        </div>

                        {{-- 作品画像 --}}
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $animeTitle->title }}" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                        @else
                            <div style="width: 50px; height: 70px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 0.7em; text-align: center;">
                                No Image
                            </div>
                        @endif

                        {{-- 作品情報 --}}
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: bold; font-size: 1.05em; margin-bottom: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $animeTitle->title }}">
                                {{ $animeTitle->title }}
                            </div>
                            <div style="font-size: 0.85em; color: #6c757d;">
                                {{ $totalSeries }}シリーズ
                                @if($watchedCount > 0)
                                    （{{ $watchedCount }}/{{ $totalSeries }} 視聴済み）
                                @endif
                            </div>
                        </div>

                        {{-- 視聴状況アイコン --}}
                        <div style="flex: 0 0 100px; text-align: center;">
                            <div style="font-size: 2em; font-weight: bold; color: {{ $color }};" title="{{ $statusLabel }}">
                                {{ $symbol }}
                            </div>
                            <div style="font-size: 0.75em; color: {{ $color }}; font-weight: 500;">
                                {{ $statusLabel }}
                            </div>
                        </div>

                        {{-- 配信プラットフォーム --}}
                        @php
                            $platforms = AnimeTitleUtil::getPlatforms($animeTitle);
                        @endphp
                        <div style="flex: 0 0 120px; display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                            @foreach($platforms->take(4) as $platform)
                                @php $iconFile = PlatformUtil::getIconFile($platform->name); @endphp
                                @if($iconFile)
                                    <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}" style="width: 20px; height: 20px;">
                                @endif
                            @endforeach
                            @if($platforms->count() > 4)
                                <span style="font-size: 0.7em; color: #6c757d; align-self: center;">+{{ $platforms->count() - 4 }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- シリーズ詳細（デフォルトで非表示） --}}
                    <div class="work-detail" style="display: none; border-top: 1px solid #e9ecef;">
                        <div style="padding: 15px;">
                            <h4 style="font-size: 1em; color: #2c3e50; margin-bottom: 10px; font-weight: 600;">シリーズ詳細</h4>
                            <div class="table-responsive">
                                <table class="table" style="margin-bottom: 0;">
                                    <thead>
                                        <tr>
                                            <th style="width: 60%;">シリーズ名</th>
                                            <th style="width: 15%; text-align: center;">話数</th>
                                            <th style="width: 25%; text-align: center;">視聴状況</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($animeTitle->series as $series)
                                            @php
                                                $status = isset($watchStatuses[$series->id]) ? $watchStatuses[$series->id]->first() : null;
                                                $seriesSymbol = '-';
                                                $seriesColor = '#6c757d';
                                                $seriesLabel = '未視聴';

                                                if ($status) {
                                                    if ($status->status === WatchStatus::WATCHING->value) {
                                                        $seriesSymbol = '△';
                                                        $seriesColor = '#007bff';
                                                        $seriesLabel = '視聴中';
                                                    } elseif ($status->status === WatchStatus::WATCHED->value) {
                                                        $seriesSymbol = '〇';
                                                        $seriesColor = '#28a745';
                                                        $seriesLabel = '視聴済み';
                                                        if ($status->completed_at) {
                                                            $seriesLabel .= ' (' . $status->completed_at->format('Y/m/d') . ')';
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $series->name }}</td>
                                                <td style="text-align: center;">{{ $series->episodes->count() }} 話</td>
                                                <td style="text-align: center;">
                                                    <span style="font-size: 1.3em; font-weight: bold; color: {{ $seriesColor }}; margin-right: 5px;" title="{{ $seriesLabel }}">
                                                        {{ $seriesSymbol }}
                                                    </span>
                                                    <span style="font-size: 0.85em; color: {{ $seriesColor }};">
                                                        {{ $seriesLabel }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <script>
        function toggleWorkDetail(header) {
            const card = header.closest('.work-status-card');
            const detail = card.querySelector('.work-detail');
            const icon = header.querySelector('.toggle-icon');

            if (detail.style.display === 'none') {
                detail.style.display = 'block';
                icon.style.transform = 'rotate(90deg)';
                header.style.backgroundColor = '#f8f9fa';
            } else {
                detail.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
                header.style.backgroundColor = 'transparent';
            }
        }
    </script>
@endsection
