@extends('layouts.app')

@section('title', ($member->exists ? 'メンバー編集' : 'メンバー追加') . ' - アニメ管理システム')

@section('content')
    <h1 class="page-title">{{ $member->exists ? 'メンバー編集' : 'メンバー追加' }}</h1>

    <div class="form-container">
        <form method="POST" action="{{ $member->exists ? route('members.update', $member) : route('members.store') }}">
            @csrf
            @if($member->exists)
                @method('PUT')
            @endif

            <div class="form-group">
                <label class="form-label" for="name">名前 <span style="color: #e74c3c;">*</span></label>
                <input type="text" id="name" name="name" class="form-control" placeholder="例：山田太郎" value="{{ old('name', $member->name) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="is_active">アクティブ</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', $member->is_active ?? true) == 1 ? 'selected' : '' }}>有効</option>
                    <option value="0" {{ old('is_active', $member->is_active ?? true) == 0 ? 'selected' : '' }}>無効</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">保存</button>
                <a href="{{ $member->exists ? route('members.show', $member) : route('members.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
