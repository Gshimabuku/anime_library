@extends('layouts.app')

@section('title', '配信プラットフォーム一覧 - アニメ管理システム')

@section('content')
    @php use App\Utils\PlatformUtil; @endphp
    <h1 class="page-title">配信プラットフォーム一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <form method="GET" action="{{ route('platforms.index') }}" class="search-box">
            <input type="text" name="keyword" class="search-input" placeholder="プラットフォーム名で検索..." value="{{ $keyword ?? '' }}">
            <button type="submit" class="btn btn-secondary">検索</button>
        </form>
        <a href="{{ route('platforms.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- プラットフォームテーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>プラットフォーム名</th>
                    <th>配信作品数</th>
                    <th>ステータス</th>
                </tr>
            </thead>
            <tbody>
                @forelse($platforms as $platform)
                    <tr class="clickable-row" onclick="location.href='{{ route('platforms.show', $platform) }}'">
                        <td>P{{ str_pad($platform->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $platform->name }}</td>
                        <td>{{ PlatformUtil::getAnimeTitleCount($platform) }}作品</td>
                        <td>
                            @if($platform->is_active)
                                <span class="badge badge-active">有効</span>
                            @else
                                <span class="badge badge-inactive">停止中</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">プラットフォームが見つかりません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($platforms->hasPages())
        <div style="margin-top:20px;text-align:center;">
            {{ $platforms->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
