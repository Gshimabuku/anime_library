<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnimeTitleController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\WatchStatusController;

// ダッシュボード
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// 視聴状況一覧
Route::get('/watch-status', [WatchStatusController::class, 'index'])->name('watch-status.index');

// メンバー管理
Route::resource('members', MemberController::class);
Route::get('members/{member}/watch-status', [MemberController::class, 'watchStatus'])->name('members.watch-status');
Route::patch('members/{member}/watch-status', [MemberController::class, 'updateWatchStatus'])->name('members.watch-status.update');

// 作品管理
Route::resource('works', AnimeTitleController::class)->parameters([
    'works' => 'animeTitle',
]);
Route::get('works/{animeTitle}/watch-status', [AnimeTitleController::class, 'watchStatus'])->name('works.watch-status');
Route::post('works/csv-import', [AnimeTitleController::class, 'csvImport'])->name('works.csv-import');
Route::post('works/{animeTitle}/series-csv-import', [AnimeTitleController::class, 'seriesCsvImport'])->name('works.series-csv-import');

// エピソードCSVインポート
Route::get('series/{series}/episodes/csv-import', [EpisodeController::class, 'csvImportForm'])->name('episodes.csv-import-form');
Route::post('series/{series}/episodes/csv-import', [EpisodeController::class, 'csvImport'])->name('episodes.csv-import');

// 配信プラットフォーム管理
Route::resource('platforms', PlatformController::class);
