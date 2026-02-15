@extends('layouts.app')

@section('title', 'メンバー一覧 - アニメ管理システム')

@section('content')
    <h1 class="page-title">メンバー一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <div></div>
        <a href="{{ route('members.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- 検索パネル -->
    <x-search-panel>
        <form method="GET" action="{{ route('members.index') }}" id="searchForm">
            <div class="search-form-grid">
                <div class="search-field">
                    <label class="search-field-label">名前</label>
                    <input type="text" name="keyword" class="form-control form-control-sm" value="{{ $searchParams['keyword'] ?? '' }}" placeholder="部分一致">
                </div>
                <div class="search-field">
                    <label class="search-field-label">視聴済み作品数</label>
                    <div class="search-range">
                        <input type="number" name="watched_count_min" class="form-control form-control-sm" value="{{ $searchParams['watched_count_min'] ?? '' }}" placeholder="以上" min="0">
                        <span class="search-range-separator">～</span>
                        <input type="number" name="watched_count_max" class="form-control form-control-sm" value="{{ $searchParams['watched_count_max'] ?? '' }}" placeholder="以下" min="0">
                    </div>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary btn-sm">検索</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetSearch('{{ route('members.index') }}')">リセット</button>
            </div>
        </form>
    </x-search-panel>

    <!-- メンバーテーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>視聴済み作品数</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr class="clickable-row" onclick="location.href='{{ route('members.show', $member) }}'">
                        <td>{{ str_pad($member->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $member->name }}</td>
                        <td class="text-center">{{ $member->watched_anime_titles_count ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">メンバーが見つかりません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->hasPages())
        @include('components.pagination', ['paginator' => $members->appends(request()->query())])
    @endif
@endsection
