@extends('layouts.app')

@section('title', '視聴状況一覧 - アニメ管理システム')

@section('content')
    @php
        use App\Enums\WatchStatus;
        use App\Utils\AnimeTitleUtil;
        use App\Utils\PlatformUtil;
    @endphp

    <h1 class="page-title">視聴状況一覧</h1>

    <!-- 検索パネル -->
    <x-search-panel>
        <form method="GET" action="{{ route('watch-status.index') }}" id="searchForm">
            <div class="search-form-grid">
                <div class="search-field">
                    <label class="search-field-label">作品名</label>
                    <input type="text" name="keyword" class="form-control form-control-sm" value="{{ $searchParams['keyword'] ?? '' }}" placeholder="部分一致">
                </div>
                <div class="search-field">
                    <label class="search-field-label">視聴状況</label>
                    <div class="search-checkbox-group">
                        @foreach(WatchStatus::cases() as $status)
                            <label class="checkbox-label">
                                <input type="checkbox" name="watch_statuses[]" value="{{ $status->value }}" {{ in_array($status->value, array_map('intval', $searchParams['watch_statuses'] ?? [])) ? 'checked' : '' }}>
                                {{ $status->label() }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary btn-sm">検索</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetSearch('{{ route('watch-status.index') }}')">リセット</button>
            </div>
        </form>
    </x-search-panel>

    <div class="list-container">
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
            <p style="text-align: center; padding: 40px; color: #6c757d;">作品が登録されていません。</p>
        @elseif($members->isEmpty())
            <p style="text-align: center; padding: 40px; color: #6c757d;">アクティブなメンバーがいません。</p>
        @else
            {{-- メンバーヘッダー --}}
            <div style="display: flex; gap: 10px; margin-bottom: 20px; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); align-items: center; position: sticky; top: 0; z-index: 10;">
                <div style="flex: 0 0 200px; font-weight: bold; font-size: 1.1em;">作品名</div>
                <div style="flex: 1; display: flex; gap: 15px; justify-content: center;">
                    @foreach($members as $member)
                        <div style="flex: 1; text-align: center; font-weight: bold; min-width: 80px;">
                            <a href="{{ route('members.watch-status', $member) }}" style="text-decoration: none; color: #007bff;">
                                {{ $member->name }}
                            </a>
                        </div>
                    @endforeach
                </div>
                <div style="flex: 0 0 100px; text-align: center; font-weight: bold;">詳細</div>
            </div>

            {{-- 統計情報 --}}
            <div style="display: flex; gap: 10px; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); align-items: center;">
                <div style="flex: 0 0 200px; font-weight: bold;">統計</div>
                <div style="flex: 1; display: flex; gap: 15px; justify-content: center;">
                    @foreach($members as $member)
                        @php
                            $watchedCount = 0;
                            $partialCount = 0;
                            $unwatchedCount = 0;

                            foreach($animeTitles as $animeTitle) {
                                $seriesWatchedCount = 0;
                                $totalSeries = $animeTitle->series->count();

                                foreach($animeTitle->series as $series) {
                                    $status = null;
                                    if (isset($allWatchStatuses[$series->id])) {
                                        $status = $allWatchStatuses[$series->id]->firstWhere('member_id', $member->id);
                                    }

                                    if ($status && $status->status === WatchStatus::WATCHED->value) {
                                        $seriesWatchedCount++;
                                    }
                                }

                                if ($seriesWatchedCount === $totalSeries && $totalSeries > 0) {
                                    $watchedCount++;
                                } elseif ($seriesWatchedCount > 0) {
                                    $partialCount++;
                                } else {
                                    $unwatchedCount++;
                                }
                            }
                        @endphp
                        <div style="flex: 1; text-align: center; font-size: 0.9em; min-width: 80px;">
                            <span style="color: #28a745; font-weight: bold;">〇 {{ $watchedCount }}</span> /
                            <span style="color: #007bff; font-weight: bold;">△ {{ $partialCount }}</span> /
                            <span style="color: #6c757d; font-weight: bold;">- {{ $unwatchedCount }}</span>
                        </div>
                    @endforeach
                </div>
                <div style="flex: 0 0 100px;"></div>
            </div>

            {{-- 作品リスト --}}
            @foreach($animeTitles as $animeTitle)
                <div class="work-status-card" style="display: flex; gap: 10px; margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); align-items: center;">
                    {{-- 作品情報 --}}
                    <div style="flex: 0 0 200px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @php
                                $imageUrl = $animeTitle->image_url;
                                if (!$imageUrl) {
                                    $imagePath = 'images/works/works_' . str_pad($animeTitle->id, 3, '0', STR_PAD_LEFT) . '.jpg';
                                    $imageUrl = file_exists(public_path($imagePath)) ? asset($imagePath) : null;
                                }
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $animeTitle->title }}" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                            @else
                                <div style="width: 50px; height: 70px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 0.8em;">
                                    No Image
                                </div>
                            @endif
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: bold; margin-bottom: 5px; font-size: 0.95em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $animeTitle->title }}">
                                    {{ $animeTitle->title }}
                                </div>
                                @php
                                    $platforms = AnimeTitleUtil::getPlatforms($animeTitle);
                                @endphp
                                <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                                    @foreach($platforms->take(3) as $platform)
                                        @php $iconFile = PlatformUtil::getIconFile($platform->name); @endphp
                                        @if($iconFile)
                                            <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}" style="width: 16px; height: 16px;">
                                        @endif
                                    @endforeach
                                    @if($platforms->count() > 3)
                                        <span style="font-size: 0.7em; color: #6c757d;">+{{ $platforms->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- メンバーごとの視聴状況 --}}
                    <div style="flex: 1; display: flex; gap: 15px; justify-content: center;">
                        @foreach($members as $member)
                            @php
                                // この作品の全シリーズに対するメンバーの視聴状況を集計
                                $watchedCount = 0;
                                $totalSeries = $animeTitle->series->count();

                                foreach($animeTitle->series as $series) {
                                    $status = null;
                                    if (isset($allWatchStatuses[$series->id])) {
                                        $status = $allWatchStatuses[$series->id]->firstWhere('member_id', $member->id);
                                    }

                                    if ($status && $status->status === WatchStatus::WATCHED->value) {
                                        $watchedCount++;
                                    }
                                }

                                // 表示する記号と色を決定
                                $symbol = '-';
                                $color = '#6c757d';
                                $title = '未視聴';

                                if ($watchedCount === $totalSeries && $totalSeries > 0) {
                                    // すべてのシリーズが視聴済み
                                    $symbol = '〇';
                                    $color = '#28a745';
                                    $title = "視聴済み ({$watchedCount}/{$totalSeries})";
                                } elseif ($watchedCount > 0) {
                                    // 一部のシリーズが視聴済み
                                    $symbol = '△';
                                    $color = '#007bff';
                                    $title = "一部視聴 (視聴済み: {$watchedCount}/{$totalSeries})";
                                }
                            @endphp
                            <div style="flex: 1; text-align: center; font-size: 1.5em; font-weight: bold; color: {{ $color }}; min-width: 80px;" title="{{ $title }}">
                                {{ $symbol }}
                            </div>
                        @endforeach
                    </div>

                    {{-- 詳細リンク --}}
                    <div style="flex: 0 0 100px; text-align: center;">
                        <a href="{{ route('works.watch-status', $animeTitle) }}" class="btn btn-sm btn-info" style="font-size: 0.85em; padding: 5px 10px;">詳細</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
