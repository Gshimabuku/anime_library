@extends('layouts.app')

@section('title', '作品一覧 - アニメ管理システム')

@section('content')
    <h1 class="page-title">作品一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <form method="GET" action="{{ route('works.index') }}" class="search-box">
            <input type="text" name="keyword" class="search-input" placeholder="作品名で検索..." value="{{ $keyword ?? '' }}">
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY }}" {{ in_array(\App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY, $workTypes) ? 'checked' : '' }}> シリーズ作品
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ \App\Models\AnimeTitle::WORK_TYPE_COUR_PLUS_MOVIE }}" {{ in_array(\App\Models\AnimeTitle::WORK_TYPE_COUR_PLUS_MOVIE, $workTypes) ? 'checked' : '' }}> シリーズ+映画
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="work_types[]" value="{{ \App\Models\AnimeTitle::WORK_TYPE_MOVIE_ONLY }}" {{ in_array(\App\Models\AnimeTitle::WORK_TYPE_MOVIE_ONLY, $workTypes) ? 'checked' : '' }}> 映画
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
                        <td>{{ $title->series_count_display }}</td>
                        <td>{{ $title->work_type == \App\Models\AnimeTitle::WORK_TYPE_MOVIE_ONLY ? '-' : $title->total_episodes }}</td>
                        <td>
                            @if($title->work_type == \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY)
                                <span class="badge badge-active">シリーズのみ</span>
                            @elseif($title->work_type == \App\Models\AnimeTitle::WORK_TYPE_COUR_PLUS_MOVIE)
                                <span class="badge badge-pending">シリーズ+映画</span>
                            @else
                                <span class="badge badge-inactive">映画のみ</span>
                            @endif
                        </td>
                        <td>{{ $title->total_duration_display }}</td>
                        <td>
                            <div class="platform-icons">
                                @foreach($title->platforms as $platform)
                                    @if($platform->icon_file)
                                        <img src="{{ asset('images/icon/' . $platform->icon_file) }}" alt="{{ $platform->name }}" title="{{ $platform->name }}">
                                    @endif
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
