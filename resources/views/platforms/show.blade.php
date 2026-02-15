@extends('layouts.app')

@section('title', 'プラットフォーム詳細 - アニメ管理システム')

@section('content')
    @php use App\Utils\PlatformUtil; @endphp
    <h1 class="page-title">配信プラットフォーム詳細</h1>

    <div class="detail-container">
        <div class="detail-header">
            <h2 class="detail-title">{{ $platform->name }}</h2>
            <div class="detail-actions">
                <a href="{{ route('platforms.edit', $platform) }}" class="btn btn-primary">編集</a>
                <form method="POST" action="{{ route('platforms.destroy', $platform) }}" style="display:inline;" onsubmit="return confirm('本当に削除しますか？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">削除</button>
                </form>
                <a href="{{ route('platforms.index') }}" class="btn btn-secondary">一覧に戻る</a>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">プラットフォームID</div>
            <div class="detail-value">P{{ str_pad($platform->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">プラットフォーム名</div>
            <div class="detail-value">{{ $platform->name }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">ステータス</div>
            <div class="detail-value">
                @if($platform->is_active)
                    <span class="badge badge-active">有効</span>
                @else
                    <span class="badge badge-inactive">停止中</span>
                @endif
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">配信作品数</div>
            <div class="detail-value">{{ PlatformUtil::getAnimeTitleCount($platform) }}作品</div>
        </div>
    </div>
@endsection
