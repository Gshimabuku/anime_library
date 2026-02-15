@extends('layouts.app')

@section('title', '作品一覧 - アニメ管理システム')

@section('content')
    @php
        use App\Enums\WorkType;
        use App\Utils\AnimeTitleUtil;
        use App\Utils\PlatformUtil;
    @endphp
    <h1 class="page-title">作品一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <form method="GET" action="{{ route('works.index') }}" class="search-box">
            <input type="text" name="keyword" class="search-input" placeholder="作品名で検索..." value="{{ $keyword ?? '' }}">
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ WorkType::SERIES_ONLY->value }}" {{ in_array(WorkType::SERIES_ONLY->value, $workTypes) ? 'checked' : '' }}> シリーズ作品
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ WorkType::SERIES_PLUS_MOVIE->value }}" {{ in_array(WorkType::SERIES_PLUS_MOVIE->value, $workTypes) ? 'checked' : '' }}> シリーズ+映画
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ WorkType::MOVIE_ONLY->value }}" {{ in_array(WorkType::MOVIE_ONLY->value, $workTypes) ? 'checked' : '' }}> 映画
                </label>
            </div>
            <button type="submit" class="btn btn-secondary">検索</button>
        </form>
        <a href="{{ route('works.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- 作品テーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>作品名</th>
                    <th>シリーズ数</th>
                    <th>話数</th>
                    <th>タイプ</th>
                    <th>総視聴時間</th>
                    <th>配信</th>
                </tr>
            </thead>
            <tbody>
                @forelse($titles as $title)
                    <tr class="clickable-row" onclick="location.href='{{ route('works.show', $title) }}'">
                        <td>W{{ str_pad($title->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $title->title }}</td>
                        <td>
                            @php
                                $seriesCount = AnimeTitleUtil::getSeriesCount($title);
                                $specialCount = AnimeTitleUtil::getSpecialCount($title);
                                $movieCount = AnimeTitleUtil::getMovieCount($title);
                            @endphp
                            <div style="line-height: 1.4; font-size: 0.9rem;">
                                @if($seriesCount > 0)
                                    <div>シリーズ：{{ $seriesCount }}</div>
                                @endif
                                @if($specialCount > 0)
                                    <div>スペシャル：{{ $specialCount }}</div>
                                @endif
                                @if($movieCount > 0)
                                    <div>映画：{{ $movieCount }}</div>
                                @endif
                            </div>
                        </td>
                        <td>{{ $title->work_type == WorkType::MOVIE_ONLY->value ? '-' : AnimeTitleUtil::getTotalEpisodes($title) }}</td>
                        <td>
                            @if($title->work_type == WorkType::SERIES_ONLY->value)
                                <span class="badge badge-active">シリーズのみ</span>
                            @elseif($title->work_type == WorkType::SERIES_PLUS_MOVIE->value)
                                <span class="badge badge-pending">シリーズ+映画</span>
                            @else
                                <span class="badge badge-inactive">映画のみ</span>
                            @endif
                        </td>
                        <td>{{ AnimeTitleUtil::getTotalDurationDisplay($title) }}</td>
                        <td>
                            @php
                                $pointRequiredPlatformIds = AnimeTitleUtil::getPointRequiredPlatformIds($title);
                                $platforms = AnimeTitleUtil::getPlatforms($title);
                            @endphp
                            <div class="platform-icons">
                                @foreach($platforms as $platform)
                                    @php $iconFile = PlatformUtil::getIconFile($platform->name); @endphp
                                    <span class="platform-icon-item{{ in_array($platform->id, $pointRequiredPlatformIds, true) ? ' point-required' : '' }}">
                                        @if($iconFile)
                                            <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}{{ in_array($platform->id, $pointRequiredPlatformIds, true) ? '（ポイント必要）' : '' }}">
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">作品が見つかりません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($titles->hasPages())
        <div style="margin-top:20px;text-align:center;">
            {{ $titles->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
