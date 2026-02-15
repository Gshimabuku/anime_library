@extends('layouts.app')

@section('title', 'メンバー詳細 - アニメ管理システム')

@section('content')
    <h1 class="page-title">メンバー詳細</h1>

    <div class="detail-container">
        <div class="detail-header">
            <h2 class="detail-title">{{ $member->name }}</h2>
            <div class="detail-actions">
                <a href="{{ route('members.watch-status', $member) }}" class="btn btn-info">視聴状況を見る</a>
                <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">編集</a>
                <form method="POST" action="{{ route('members.destroy', $member) }}" style="display:inline;" onsubmit="return confirm('本当に削除しますか？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">削除</button>
                </form>
                <a href="{{ route('members.index') }}" class="btn btn-secondary">一覧に戻る</a>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">ID</div>
            <div class="detail-value">{{ str_pad($member->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $member->name }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">アクティブ</div>
            <div class="detail-value">
                @if($member->is_active)
                    <span class="badge badge-active">有効</span>
                @else
                    <span class="badge badge-inactive">無効</span>
                @endif
            </div>
        </div>
    </div>
@endsection
