@extends('layouts.app')

@section('title', '作品編集 - アニメ管理システム')

@section('content')
    @php
        use App\Enums\WorkType;
        use App\Enums\SeriesFormatType;
        use App\Enums\WatchCondition;
    @endphp
    <h1 class="page-title">作品編集</h1>

    <div class="edit-form-container">
        <form method="POST" action="{{ route('works.update', $animeTitle) }}" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')

            {{-- ============================== --}}
            {{-- 作品基本情報セクション --}}
            {{-- ============================== --}}
            <div class="edit-section">
                <h2 class="edit-section-title">作品基本情報</h2>

                <div class="edit-form-row">
                    <div class="edit-form-group flex-2">
                        <label class="form-label" for="title">作品名 <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control"
                               placeholder="例：冒険者たちの物語"
                               value="{{ old('title', $animeTitle->title) }}">
                    </div>
                    <div class="edit-form-group flex-2">
                        <label class="form-label" for="title_kana">作品名（かな）</label>
                        <input type="text" id="title_kana" name="title_kana" class="form-control"
                               placeholder="例：ぼうけんしゃたちのものがたり"
                               value="{{ old('title_kana', $animeTitle->title_kana) }}">
                    </div>
                </div>

                <div class="edit-form-row">
                    <div class="edit-form-group flex-1">
                        <label class="form-label" for="work_type">作品タイプ <span class="required">*</span></label>
                        <select id="work_type" name="work_type" class="form-control">
                            @foreach(WorkType::cases() as $type)
                                <option value="{{ $type->value }}"
                                    {{ old('work_type', $animeTitle->work_type) == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="edit-form-group flex-1">
                        <label class="form-label" for="image">作品画像</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        @if($animeTitle->image_url)
                            <div style="margin-top: 8px;">
                                <img src="{{ $animeTitle->image_url }}" alt="{{ $animeTitle->title }}" style="max-width: 120px; border-radius: 6px;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================== --}}
            {{-- シリーズ一覧セクション --}}
            {{-- ============================== --}}
            <div class="edit-section">
                <div class="edit-section-header">
                    <h2 class="edit-section-title">シリーズ一覧</h2>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addSeries()">＋ シリーズ追加</button>
                </div>

                <div id="seriesList">
                    @foreach($animeTitle->series as $sIndex => $series)
                        <div class="series-edit-block collapsed" data-series-index="{{ $sIndex }}" draggable="true">
                            <input type="hidden" name="series[{{ $sIndex }}][id]" value="{{ $series->id }}">
                            <input type="hidden" name="series[{{ $sIndex }}][series_order]" value="{{ $series->series_order }}" class="series-order-input">

                            <div class="series-edit-header">
                                <span class="drag-handle series-drag" title="ドラッグして並び替え">☰</span>
                                <span class="toggle-icon collapsible" onclick="toggleEditSeries(this)" title="折りたたみ">▶</span>
                                <div class="series-edit-fields">
                                    <div class="edit-form-group flex-2">
                                        <label class="form-label">シリーズ名 <span class="required">*</span></label>
                                        <input type="text" name="series[{{ $sIndex }}][name]" class="form-control"
                                               value="{{ old("series.{$sIndex}.name", $series->name) }}">
                                    </div>
                                    <div class="edit-form-group flex-1">
                                        <label class="form-label">フォーマット <span class="required">*</span></label>
                                        <select name="series[{{ $sIndex }}][format_type]" class="form-control">
                                            @foreach(SeriesFormatType::cases() as $fmt)
                                                <option value="{{ $fmt->value }}"
                                                    {{ old("series.{$sIndex}.format_type", $series->format_type) == $fmt->value ? 'selected' : '' }}>
                                                    {{ $fmt->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removeSeries(this)" title="シリーズ削除">✕</button>
                            </div>

                            {{-- 配信PFセクション --}}
                            <div class="subsection series-collapsible-content">
                                <div class="subsection-header">
                                    <h4 class="subsection-title">配信プラットフォーム</h4>
                                    <button type="button" class="btn btn-outline btn-xs" onclick="addPlatform(this, {{ $sIndex }})">＋ 追加</button>
                                </div>
                                <div class="platform-edit-list" data-series-index="{{ $sIndex }}">
                                    @php
                                        $spas = $series->seriesPlatformAvailabilities ?? collect();
                                    @endphp
                                    @foreach($spas as $pIndex => $spa)
                                        <div class="platform-edit-row">
                                            <select name="series[{{ $sIndex }}][platforms][{{ $pIndex }}][platform_id]" class="form-control form-control-sm">
                                                @foreach($platforms as $pf)
                                                    <option value="{{ $pf->id }}" {{ $spa->platform_id == $pf->id ? 'selected' : '' }}>{{ $pf->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="series[{{ $sIndex }}][platforms][{{ $pIndex }}][watch_condition]" class="form-control form-control-sm">
                                                @foreach(WatchCondition::cases() as $wc)
                                                    <option value="{{ $wc->value }}" {{ $spa->watch_condition === $wc ? 'selected' : '' }}>{{ $wc->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="this.closest('.platform-edit-row').remove()">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- エピソードセクション --}}
                            <div class="subsection series-collapsible-content">
                                <div class="subsection-header">
                                    <h4 class="subsection-title">エピソード</h4>
                                    <button type="button" class="btn btn-outline btn-xs" onclick="addEpisode(this, {{ $sIndex }})">＋ 追加</button>
                                </div>
                                <div class="episode-edit-list" data-series-index="{{ $sIndex }}">
                                    @if($series->episodes->count() > 0)
                                        <div class="episode-edit-header-row">
                                            <span class="ep-col-handle"></span>
                                            <span class="ep-col-no">話数</span>
                                            <span class="ep-col-title">サブタイトル</span>
                                            <span class="ep-col-year">放送年</span>
                                            <span class="ep-col-dur">尺(分)</span>
                                            <span class="ep-col-action"></span>
                                        </div>
                                    @endif
                                    @foreach($series->episodes as $eIndex => $episode)
                                        <div class="episode-edit-row" draggable="true">
                                            <input type="hidden" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][id]" value="{{ $episode->id }}">
                                            <span class="drag-handle episode-drag" title="ドラッグして並び替え">☰</span>
                                            <input type="number" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][episode_no]" class="form-control form-control-sm ep-col-no episode-no-input"
                                                   value="{{ old("series.{$sIndex}.episodes.{$eIndex}.episode_no", $episode->episode_no) }}" min="1">
                                            <input type="text" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][episode_title]" class="form-control form-control-sm ep-col-title"
                                                   value="{{ old("series.{$sIndex}.episodes.{$eIndex}.episode_title", $episode->episode_title) }}" placeholder="サブタイトル">
                                            <input type="number" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][onair_date]" class="form-control form-control-sm ep-col-year"
                                                   value="{{ old("series.{$sIndex}.episodes.{$eIndex}.onair_date", $episode->onair_date) }}" placeholder="年">
                                            <input type="number" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][duration_min]" class="form-control form-control-sm ep-col-dur"
                                                   value="{{ old("series.{$sIndex}.episodes.{$eIndex}.duration_min", $episode->duration_min) }}" min="1">
                                            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="removeEpisode(this)">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- アークセクション --}}
                            <div class="subsection series-collapsible-content">
                                <div class="subsection-header">
                                    <h4 class="subsection-title">アーク（編）</h4>
                                    <button type="button" class="btn btn-outline btn-xs" onclick="addArc(this, {{ $sIndex }})">＋ 追加</button>
                                </div>
                                <div class="arc-edit-list" data-series-index="{{ $sIndex }}">
                                    @foreach(($series->arcs ?? collect()) as $aIndex => $arc)
                                        <div class="arc-edit-row">
                                            <input type="hidden" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][id]" value="{{ $arc->id }}">
                                            <input type="text" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][name]" class="form-control form-control-sm arc-col-name"
                                                   value="{{ old("series.{$sIndex}.arcs.{$aIndex}.name", $arc->name) }}" placeholder="アーク名">
                                            <input type="number" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][start_episode_no]" class="form-control form-control-sm arc-col-ep"
                                                   value="{{ old("series.{$sIndex}.arcs.{$aIndex}.start_episode_no", $arc->start_episode_no) }}" placeholder="開始" min="1">
                                            <span class="arc-separator">〜</span>
                                            <input type="number" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][end_episode_no]" class="form-control form-control-sm arc-col-ep"
                                                   value="{{ old("series.{$sIndex}.arcs.{$aIndex}.end_episode_no", $arc->end_episode_no) }}" placeholder="終了" min="1">
                                            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="removeArc(this)">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 削除トラッキング用hidden --}}
            <div id="deletedIds">
                {{-- JS で動的に追加 --}}
            </div>

            {{-- 保存ボタン --}}
            <div class="edit-form-actions">
                <button type="submit" class="btn btn-success">保存</button>
                <a href="{{ route('works.show', $animeTitle) }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>

    {{-- テンプレート用データ --}}
    <script>
        const platformOptions = @json($platforms->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
        const formatTypeOptions = @json(array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], SeriesFormatType::cases()));
        const watchConditionOptions = @json(array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], WatchCondition::cases()));
    </script>
    <script src="{{ asset('js/work-edit.js') }}"></script>
@endsection
