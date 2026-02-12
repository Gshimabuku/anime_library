@extends('layouts.app')

@section('title', 'アニメ管理システム - ホーム')

@section('content')
    <h1 class="page-title">ダッシュボード</h1>

    <!-- 統計カード -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number">{{ $memberCount }}</div>
            <div class="stat-label">メンバー数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $titleCount }}</div>
            <div class="stat-label">作品数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $platformCount }}</div>
            <div class="stat-label">配信プラットフォーム</div>
        </div>
    </div>

    <!-- クイックアクセス -->
    <h2 class="page-title" style="font-size: 1.4rem;">クイックアクセス</h2>
    <div class="card-container">
        <div class="card">
            <h3 class="card-title">メンバー管理</h3>
            <p class="card-text">メンバー情報を管理します。</p>
            <a href="{{ route('members.index') }}" class="card-link">一覧を見る →</a>
        </div>
        <div class="card">
            <h3 class="card-title">作品管理</h3>
            <p class="card-text">アニメ作品の情報を登録・編集します。</p>
            <a href="{{ route('works.index') }}" class="card-link">一覧を見る →</a>
        </div>
        <div class="card">
            <h3 class="card-title">配信プラットフォーム</h3>
            <p class="card-text">配信サービスの情報を管理します。</p>
            <a href="{{ route('platforms.index') }}" class="card-link">一覧を見る →</a>
        </div>
    </div>
@endsection
