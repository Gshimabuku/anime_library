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
        <div></div>
        <a href="{{ route('works.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- 検索パネル -->
    <x-search-panel>
        <form method="GET" action="{{ route('works.index') }}" id="searchForm">
            <div class="search-form-grid">
                <div class="search-field">
                    <label class="search-field-label">作品名</label>
                    <input type="text" name="keyword" class="form-control form-control-sm" value="{{ $searchParams['keyword'] ?? '' }}" placeholder="部分一致">
                </div>
                <div class="search-field">
                    <label class="search-field-label">シリーズ数</label>
                    <div class="search-range">
                        <input type="number" name="series_count_min" class="form-control form-control-sm" value="{{ $searchParams['series_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="series_count_max" class="form-control form-control-sm" value="{{ $searchParams['series_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
                <div class="search-field">
                    <label class="search-field-label">スペシャル数</label>
                    <div class="search-range">
                        <input type="number" name="special_count_min" class="form-control form-control-sm" value="{{ $searchParams['special_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="special_count_max" class="form-control form-control-sm" value="{{ $searchParams['special_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
                <div class="search-field">
                    <label class="search-field-label">映画数</label>
                    <div class="search-range">
                        <input type="number" name="movie_count_min" class="form-control form-control-sm" value="{{ $searchParams['movie_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="movie_count_max" class="form-control form-control-sm" value="{{ $searchParams['movie_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
                <div class="search-field">
                    <label class="search-field-label">話数</label>
                    <div class="search-range">
                        <input type="number" name="episode_count_min" class="form-control form-control-sm" value="{{ $searchParams['episode_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="episode_count_max" class="form-control form-control-sm" value="{{ $searchParams['episode_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
                <div class="search-field">
                    <label class="search-field-label">タイプ</label>
                    <div class="search-checkbox-group">
                        @foreach(WorkType::cases() as $type)
                            <label class="checkbox-label">
                                <input type="checkbox" name="work_types[]" value="{{ $type->value }}" {{ in_array($type->value, $searchParams['work_types'] ?? []) ? 'checked' : '' }}>
                                {{ $type->label() }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="search-field">
                    <label class="search-field-label">総視聴時間（分）</label>
                    <div class="search-range">
                        <input type="number" name="duration_min" class="form-control form-control-sm" value="{{ $searchParams['duration_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="duration_max" class="form-control form-control-sm" value="{{ $searchParams['duration_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
                <div class="search-field search-field-full">
                    <label class="search-field-label">配信プラットフォーム</label>
                    <div class="search-checkbox-group">
                        @foreach($platforms as $platform)
                            <label class="checkbox-label">
                                <input type="checkbox" name="platform_ids[]" value="{{ $platform->id }}" {{ in_array($platform->id, $searchParams['platform_ids'] ?? []) ? 'checked' : '' }}>
                                {{ $platform->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary btn-sm">検索</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetSearch('{{ route('works.index') }}')">リセット</button>
            </div>
        </form>
    </x-search-panel>

    <!-- 作品テーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th-image"></th>
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
                        <td class="td-image">
                            @php
                                $imageUrl = $title->image_url;
                                if (!$imageUrl) {
                                    $imagePath = 'images/works/works_' . str_pad($title->id, 3, '0', STR_PAD_LEFT) . '.jpg';
                                    $imageUrl = file_exists(public_path($imagePath)) ? asset($imagePath) : null;
                                }
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $title->title }}" class="list-thumbnail">
                            @else
                                <div class="list-thumbnail-placeholder">No Image</div>
                            @endif
                        </td>
                        <td><div class="title-truncate" title="{{ $title->title }}">{{ $title->title }}</div></td>
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
                                $titlePlatforms = AnimeTitleUtil::getPlatforms($title);
                            @endphp
                            <div class="platform-icons">
                                @foreach($titlePlatforms->take(4) as $platform)
                                    @php $iconFile = PlatformUtil::getIconFile($platform->name); @endphp
                                    <span class="platform-icon-item{{ in_array($platform->id, $pointRequiredPlatformIds, true) ? ' point-required' : '' }}">
                                        @if($iconFile)
                                            <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}{{ in_array($platform->id, $pointRequiredPlatformIds, true) ? '（ポイント必要）' : '' }}">
                                        @endif
                                    </span>
                                @endforeach
                                @if($titlePlatforms->count() > 4)
                                    <span class="platform-more-badge" title="{{ $titlePlatforms->skip(4)->pluck('name')->implode(', ') }}">
                                        +{{ $titlePlatforms->count() - 4 }}
                                    </span>
                                @endif
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
        @include('components.pagination', ['paginator' => $titles->appends(request()->query())])
    @endif
@endsection
