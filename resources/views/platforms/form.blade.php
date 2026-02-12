@extends('layouts.app')

@section('title', ($platform->exists ? 'プラットフォーム編集' : 'プラットフォーム追加') . ' - アニメ管理システム')

@section('content')
    <h1 class="page-title">{{ $platform->exists ? 'プラットフォーム編集' : 'プラットフォーム追加' }}</h1>

    <div class="form-container">
        <form method="POST" action="{{ $platform->exists ? route('platforms.update', $platform) : route('platforms.store') }}">
            @csrf
            @if($platform->exists)
                @method('PUT')
            @endif

            <div class="form-group">
                <label class="form-label" for="name">プラットフォーム名 <span style="color: #e74c3c;">*</span></label>
                <input type="text" id="name" name="name" class="form-control" placeholder="例：Netflix" value="{{ old('name', $platform->name) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="is_active">ステータス</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', $platform->is_active ?? true) == 1 ? 'selected' : '' }}>有効</option>
                    <option value="0" {{ old('is_active', $platform->is_active ?? true) == 0 ? 'selected' : '' }}>停止中</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">保存</button>
                <a href="{{ $platform->exists ? route('platforms.show', $platform) : route('platforms.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
