<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'アニメ管理システム')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="header-content">
            <a href="{{ route('dashboard') }}" class="logo">🎬 アニメ管理システム</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">ホーム</a></li>
                    <li><a href="{{ route('members.index') }}" class="{{ request()->routeIs('members.*') ? 'active' : '' }}">メンバー</a></li>
                    <li><a href="{{ route('works.index') }}" class="{{ request()->routeIs('works.*') ? 'active' : '' }}">作品</a></li>
                    <li><a href="{{ route('platforms.index') }}" class="{{ request()->routeIs('platforms.*') ? 'active' : '' }}">配信プラットフォーム</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-content">
        @if(session('success'))
            <div class="alert-success" style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:4px;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-error" style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:4px;margin-bottom:20px;">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-error" style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:4px;margin-bottom:20px;">
                <ul style="margin:0;padding-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
