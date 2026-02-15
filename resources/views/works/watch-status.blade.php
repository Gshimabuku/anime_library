@extends('layouts.app')

@section('title', '視聴状況 - ' . $animeTitle->title . ' - アニメ管理システム')

@php
    use App\Enums\WatchStatus;
@endphp

@section('content')
    <h1 class="page-title">{{ $animeTitle->title }} の視聴状況</h1>

    <div class="detail-container">
        <div class="detail-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <a href="{{ route('works.show', $animeTitle) }}" class="btn btn-secondary">作品詳細に戻る</a>
                <a href="{{ route('works.index') }}" class="btn btn-secondary">作品一覧に戻る</a>
            </div>
        </div>

        {{-- 凡例 --}}
        <div style="display: flex; gap: 30px; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold;">-</span>
                <span>未視聴</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold; color: #007bff;">△</span>
                <span>視聴中</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2em; font-weight: bold; color: #28a745;">〇</span>
                <span>視聴済み</span>
            </div>
        </div>

        @if($animeTitle->series->isEmpty())
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px;">
                <p style="color: #6c757d; font-size: 1.1em; margin: 0;">シリーズ情報がありません。</p>
            </div>
        @elseif($members->isEmpty())
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px;">
                <p style="color: #6c757d; font-size: 1.1em; margin: 0;">アクティブなメンバーがいません。</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 30%;">シリーズ名</th>
                            <th style="width: 10%; text-align: center;">話数</th>
                            @foreach($members as $member)
                                <th style="text-align: center; width: {{ 60 / $members->count() }}%;">{{ $member->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($animeTitle->series as $series)
                            <tr>
                                <td>{{ $series->name }}</td>
                                <td style="text-align: center;">{{ $series->episodes->count() }} 話</td>
                                @foreach($members as $member)
                                    @php
                                        // このシリーズに対するこのメンバーの視聴状況を取得
                                        $status = null;
                                        if (isset($watchStatuses[$series->id])) {
                                            $status = $watchStatuses[$series->id]->firstWhere('member_id', $member->id);
                                        }

                                        // 視聴状況に応じて記号と色を設定
                                        $symbol = '-';
                                        $color = '#6c757d';
                                        $title = '未視聴';

                                        if ($status) {
                                            if ($status->status === WatchStatus::WATCHING->value) {
                                                $symbol = '△';
                                                $color = '#007bff';
                                                $title = '視聴中';
                                            } elseif ($status->status === WatchStatus::WATCHED->value) {
                                                $symbol = '〇';
                                                $color = '#28a745';
                                                $title = '視聴済み';
                                            }
                                        }
                                    @endphp
                                    <td style="text-align: center; font-size: 1.3em; font-weight: bold; color: {{ $color }};" title="{{ $title }}">
                                        {{ $symbol }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
