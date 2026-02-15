<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnimeTitleController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\WatchStatusController;

// ダッシュボード
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// 視聴状況一覧
Route::get('/watch-status', [WatchStatusController::class, 'index'])->name('watch-status.index');

// メンバー管理
Route::resource('members', MemberController::class);
Route::get('members/{member}/watch-status', [MemberController::class, 'watchStatus'])->name('members.watch-status');

// 作品管理
Route::resource('works', AnimeTitleController::class)->parameters([
    'works' => 'animeTitle',
]);
Route::get('works/{animeTitle}/watch-status', [AnimeTitleController::class, 'watchStatus'])->name('works.watch-status');

// 配信プラットフォーム管理
Route::resource('platforms', PlatformController::class);
