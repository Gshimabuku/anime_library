<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnimeTitleController;
use App\Http\Controllers\PlatformController;

// ダッシュボード
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// メンバー管理
Route::resource('members', MemberController::class);

// 作品管理
Route::resource('works', AnimeTitleController::class)->parameters([
    'works' => 'animeTitle',
]);

// 配信プラットフォーム管理
Route::resource('platforms', PlatformController::class);
