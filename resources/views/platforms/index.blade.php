@extends('layouts.app')

@section('title', '配信プラットフォーム一覧 - アニメ管理システム')

@section('content')
    @php use App\Utils\PlatformUtil; @endphp
    <h1 class="page-title">配信プラットフォーム一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <div></div>
        <a href="{{ route('platforms.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- 検索パネル -->
    <x-search-panel>
        <form method="GET" action="{{ route('platforms.index') }}" id="searchForm">
            <div class="search-form-grid">
                <div class="search-field">
                    <label class="search-field-label">プラットフォーム名</label>
                    <input type="text" name="keyword" class="form-control form-control-sm" value="{{ $searchParams['keyword'] ?? '' }}" placeholder="部分一致">
                </div>
                <div class="search-field">
                    <label class="search-field-label">配信作品数</label>
                    <div class="search-range">
                        <input type="number" name="title_count_min" class="form-control form-control-sm" value="{{ $searchParams['title_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="title_count_max" class="form-control form-control-sm" value="{{ $searchParams['title_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary btn-sm">検索</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetSearch('{{ route('platforms.index') }}')">リセット</button>
            </div>
        </form>
    </x-search-panel>

    <!-- プラットフォームテーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>プラットフォーム名</th>
                    <th>配信作品数</th>
                </tr>
            </thead>
            <tbody>
                @forelse($platforms as $platform)
                    <tr class="clickable-row" onclick="location.href='{{ route('platforms.show', $platform) }}'">
                        <td>P{{ str_pad($platform->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $platform->name }}</td>
                        <td>{{ PlatformUtil::getAnimeTitleCount($platform) }}作品</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">プラットフォームが見つかりません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($platforms->hasPages())
        @include('components.pagination', ['paginator' => $platforms->appends(request()->query())])
    @endif
@endsection
