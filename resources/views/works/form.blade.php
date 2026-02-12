@extends('layouts.app')

@section('title', ($animeTitle->exists ? '作品編集' : '作品追加') . ' - アニメ管理システム')

@section('content')
    <h1 class="page-title">{{ $animeTitle->exists ? '作品編集' : '作品追加' }}</h1>

    <div class="form-container">
        <form method="POST" action="{{ $animeTitle->exists ? route('works.update', $animeTitle) : route('works.store') }}">
            @csrf
            @if($animeTitle->exists)
                @method('PUT')
            @endif

            <div class="form-group">
                <label class="form-label" for="title">作品名 <span style="color: #e74c3c;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="例：冒険者たちの物語" value="{{ old('title', $animeTitle->title) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="title_kana">作品名（かな）</label>
                <input type="text" id="title_kana" name="title_kana" class="form-control" placeholder="例：ぼうけんしゃたちのものがたり" value="{{ old('title_kana', $animeTitle->title_kana) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="work_type">作品タイプ <span style="color: #e74c3c;">*</span></label>
                <select id="work_type" name="work_type" class="form-control">
                    <option value="{{ \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY }}" {{ old('work_type', $animeTitle->work_type ?? \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY) == \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY ? 'selected' : '' }}>シリーズのみ</option>
                    <option value="{{ \App\Models\AnimeTitle::WORK_TYPE_COUR_PLUS_MOVIE }}" {{ old('work_type', $animeTitle->work_type ?? \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY) == \App\Models\AnimeTitle::WORK_TYPE_COUR_PLUS_MOVIE ? 'selected' : '' }}>シリーズ+映画</option>
                    <option value="{{ \App\Models\AnimeTitle::WORK_TYPE_MOVIE_ONLY }}" {{ old('work_type', $animeTitle->work_type ?? \App\Models\AnimeTitle::WORK_TYPE_COUR_ONLY) == \App\Models\AnimeTitle::WORK_TYPE_MOVIE_ONLY ? 'selected' : '' }}>映画のみ</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">配信プラットフォーム</label>
                <div class="checkbox-group vertical">
                    @foreach($platforms as $platform)
                        <label class="checkbox-label">
                            <input type="checkbox" name="platforms[]" value="{{ $platform->id }}"
                                {{ in_array($platform->id, old('platforms', $selectedPlatforms)) ? 'checked' : '' }}>
                            {{ $platform->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="note">備考</label>
                <textarea id="note" name="note" class="form-control" placeholder="メモや補足情報を入力">{{ old('note', $animeTitle->note) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">保存</button>
                <a href="{{ $animeTitle->exists ? route('works.show', $animeTitle) : route('works.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
