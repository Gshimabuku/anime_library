@extends('layouts.app')

@section('title', '作品詳細 - アニメ管理システム')

@section('content')
    @php
        use App\Enums\SeriesFormatType;
        use App\Enums\WatchCondition;
        use App\Utils\AnimeTitleUtil;
        use App\Utils\PlatformUtil;
    @endphp
    <h1 class="page-title">作品詳細</h1>

    <div class="detail-container">
        <div class="detail-header">
            @php
                $imageUrl = $animeTitle->image_url;
                if (!$imageUrl) {
                    $imagePath = 'images/works/works_' . str_pad($animeTitle->id, 3, '0', STR_PAD_LEFT) . '.jpg';
                    $imageUrl = file_exists(public_path($imagePath)) ? asset($imagePath) : null;
                }
            @endphp
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $animeTitle->title }}" class="work-image">
            @endif
            <div class="detail-info">
                <div style="color: #999; font-size: 0.9rem; margin-bottom: 5px;">作品ID: W{{ str_pad($animeTitle->id, 3, '0', STR_PAD_LEFT) }}</div>
                <div class="detail-title-row">
                    <h2 class="detail-title">{{ $animeTitle->title }}</h2>
                    <div class="detail-actions">
                        <a href="{{ route('works.watch-status', $animeTitle) }}" class="btn btn-info">視聴状況を見る</a>
                        <a href="{{ route('works.edit', $animeTitle) }}" class="btn btn-primary">編集</a>
                        <form method="POST" action="{{ route('works.destroy', $animeTitle) }}" style="display:inline;" onsubmit="return confirm('本当に削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">削除</button>
                        </form>
                        <a href="{{ route('works.index') }}" class="btn btn-secondary">一覧に戻る</a>
                    </div>
                </div>
                @php
                    $pointRequiredPlatformIds = AnimeTitleUtil::getPointRequiredPlatformIds($animeTitle);
                    $platforms = AnimeTitleUtil::getPlatforms($animeTitle);
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
            </div>
        </div>

        <!-- シリーズ情報 -->
        @if($animeTitle->series->count() > 0)
            <div class="series-section">
                <h3 class="series-section-title">シリーズ・エピソード情報</h3>

                @foreach($animeTitle->series as $series)
                    @if($series->format_type == SeriesFormatType::MOVIE->value)
                        {{-- 映画ブロック --}}
                        <div class="series-block movie-block">
                            <div class="series-header">
                                <h4 class="series-title">{{ $series->name }}</h4>
                                @if($series->episodes->first())
                                    <span class="movie-year">{{ $series->episodes->first()->onair_date }}年</span>
                                @endif
                                <div class="platform-icons">
                                    @foreach($series->platforms as $platform)
                                        @php
                                            $watchCondition = WatchCondition::tryFrom((int) $platform->pivot->watch_condition);
                                            $isPointRequired = $watchCondition?->isPointRequired() ?? false;
                                            $iconFile = PlatformUtil::getIconFile($platform->name);
                                        @endphp
                                        <span class="platform-icon-item{{ $isPointRequired ? ' point-required' : '' }}">
                                            @if($iconFile)
                                                <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}{{ $isPointRequired ? '（ポイント必要）' : '' }}">
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="episode-list">
                                @foreach($series->episodes as $episode)
                                    <div class="episode-item">
                                        <span class="episode-duration">上映時間: {{ $episode->duration_min }}分</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- シリーズブロック --}}
                        <div class="series-block">
                            <div class="series-header">
                                <h4 class="series-title collapsible" onclick="toggleSeries(this)">
                                    <span class="toggle-icon">▼</span>
                                    {{ $series->name }}
                                </h4>
                                <div class="platform-icons">
                                    @foreach($series->platforms as $platform)
                                        @php
                                            $watchCondition = WatchCondition::tryFrom((int) $platform->pivot->watch_condition);
                                            $isPointRequired = $watchCondition?->isPointRequired() ?? false;
                                            $iconFile = PlatformUtil::getIconFile($platform->name);
                                        @endphp
                                        <span class="platform-icon-item{{ $isPointRequired ? ' point-required' : '' }}">
                                            @if($iconFile)
                                                <img src="{{ asset('images/icon/' . $iconFile) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}{{ $isPointRequired ? '（ポイント必要）' : '' }}">
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="episode-list">
                                @foreach($series->episodes as $episode)
                                    <div class="episode-item">
                                        @if($episode->episode_no)
                                            <span class="episode-number">{{ $episode->episode_no }}</span>
                                        @endif
                                        <span class="episode-title">{{ $episode->episode_title ?? '' }}</span>
                                        <span class="episode-duration">{{ $episode->duration_min }}分</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function toggleSeries(element) {
            const seriesBlock = element.closest('.series-block');
            const episodeList = seriesBlock.querySelector('.episode-list');
            const icon = element.querySelector('.toggle-icon');

            episodeList.classList.toggle('collapsed');
            seriesBlock.classList.toggle('collapsed');

            if (episodeList.classList.contains('collapsed')) {
                icon.textContent = '▶';
            } else {
                icon.textContent = '▼';
            }
        }
    </script>
@endsection
