@extends('layouts.app')

@section('title', 'メンバー一覧 - アニメ管理システム')

@section('content')
    <h1 class="page-title">メンバー一覧</h1>

    <!-- アクションバー -->
    <div class="action-bar">
        <form method="GET" action="{{ route('members.index') }}" class="search-box">
            <input type="text" name="keyword" class="search-input" placeholder="名前で検索..." value="{{ $keyword ?? '' }}">
            <button type="submit" class="btn btn-secondary">検索</button>
        </form>
        <a href="{{ route('members.create') }}" class="btn btn-primary">+ 新規追加</a>
    </div>

    <!-- メンバーテーブル -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>アクティブ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr class="clickable-row" onclick="location.href='{{ route('members.show', $member) }}'">
                        <td>{{ str_pad($member->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $member->name }}</td>
                        <td>
                            @if($member->is_active)
                                <span class="badge badge-active">有効</span>
                            @else
                                <span class="badge badge-inactive">無効</span>
                            @endif
                        </td>
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
        <div style="margin-top:20px;text-align:center;">
            {{ $members->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
