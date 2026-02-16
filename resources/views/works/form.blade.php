@extends('layouts.app')

@section('title', ($animeTitle->exists ? '作品編集' : '作品追加') . ' - アニメ管理システム')

@section('content')
    @php
        use App\Enums\SeriesFormatType;
        use App\Enums\WatchCondition;
    @endphp
    <h1 class="page-title">{{ $animeTitle->exists ? '作品編集' : '作品追加' }}</h1>

    <div class="edit-form-container">
        <form method="POST" action="{{ $animeTitle->exists ? route('works.update', $animeTitle) : route('works.store') }}" enctype="multipart/form-data" id="editForm">
            @csrf
            @if($animeTitle->exists)
                @method('PUT')
            @endif

            {{-- ============================== --}}
            {{-- 作品基本情報セクション --}}
            {{-- ============================== --}}
            <div class="edit-section">
                <div class="edit-section-header">
                    <h2 class="edit-section-title">作品基本情報</h2>
                    @if(!$animeTitle->exists)
                        <button type="button" class="btn btn-csv-import btn-sm" onclick="document.getElementById('csvImportModal').style.display='flex'">📄 CSVインポート</button>
                    @endif
                </div>

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
                        <label class="form-label" for="image">作品画像</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        @if($animeTitle->exists && $animeTitle->image_url)
                            <div class="image-preview-block" id="imagePreviewBlock" style="margin-top: 8px;">
                                <img src="{{ $animeTitle->image_url }}" alt="{{ $animeTitle->title }}" style="max-width: 120px; border-radius: 6px;">
                                <label class="image-delete-label" style="margin-top: 6px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="delete_image" value="1" id="deleteImageCheckbox">
                                    <span style="color: #e74c3c; font-size: 0.85rem;">画像を削除する</span>
                                </label>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const deleteCheckbox = document.getElementById('deleteImageCheckbox');
                                    const imageInput = document.getElementById('image');
                                    if (deleteCheckbox && imageInput) {
                                        deleteCheckbox.addEventListener('change', function () {
                                            if (this.checked) {
                                                imageInput.value = '';
                                                imageInput.disabled = true;
                                            } else {
                                                imageInput.disabled = false;
                                            }
                                        });
                                        imageInput.addEventListener('change', function () {
                                            if (this.files.length > 0) {
                                                deleteCheckbox.checked = false;
                                            }
                                        });
                                    }
                                });
                            </script>
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
                    <div class="edit-section-actions">
                        @if($animeTitle->exists)
                            <button type="button" class="btn btn-csv-import btn-sm" onclick="document.getElementById('seriesCsvImportModal').style.display='flex'">📄 CSVインポート</button>
                        @endif
                        <button type="button" class="btn btn-primary btn-sm" onclick="addSeries()">＋ シリーズ追加</button>
                    </div>
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
                                    <div class="subsection-header-actions">
                                        @if($series->exists)
                                            <a href="{{ route('episodes.csv-import-form', $series) }}" class="btn btn-csv-import btn-xs csv-import-episode-btn" data-series-id="{{ $series->id }}"
                                               {!! $series->episodes->count() > 0 ? 'style="display:none;"' : '' !!}>📄 CSVインポート</a>
                                        @endif
                                        <button type="button" class="btn btn-outline btn-xs" onclick="addEpisode(this, {{ $sIndex }})">+＋ 追加</button>
                                    </div>
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
                                            <input type="text" name="series[{{ $sIndex }}][episodes][{{ $eIndex }}][episode_no]" class="form-control form-control-sm ep-col-no episode-no-input"
                                                   value="{{ old("series.{$sIndex}.episodes.{$eIndex}.episode_no", $episode->episode_no) }}" maxlength="20">
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
                                            <input type="text" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][start_episode_no]" class="form-control form-control-sm arc-col-ep"
                                                   value="{{ old("series.{$sIndex}.arcs.{$aIndex}.start_episode_no", $arc->start_episode_no) }}" placeholder="開始" maxlength="20">
                                            <span class="arc-separator">〜</span>
                                            <input type="text" name="series[{{ $sIndex }}][arcs][{{ $aIndex }}][end_episode_no]" class="form-control form-control-sm arc-col-ep"
                                                   value="{{ old("series.{$sIndex}.arcs.{$aIndex}.end_episode_no", $arc->end_episode_no) }}" placeholder="終了" maxlength="20">
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
                <a href="{{ $animeTitle->exists ? route('works.show', $animeTitle) : route('works.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>

    {{-- テンプレート用データ --}}
    <script>
        const platformOptions = @json($platforms->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
        const formatTypeOptions = @json(array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], SeriesFormatType::cases()));
        const watchConditionOptions = @json(array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], WatchCondition::cases()));

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    </script>
    <script src="{{ asset('js/work-edit.js') }}"></script>

    @if(!$animeTitle->exists)
    {{-- 作品CSVインポートモーダル --}}
    <div id="csvImportModal" class="csv-modal-overlay" style="display:none;">
        <div class="csv-modal">
            <div class="csv-modal-header">
                <h3>作品CSVインポート</h3>
                <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="document.getElementById('csvImportModal').style.display='none'">✕</button>
            </div>
            <div class="csv-modal-body">
                <p>CSVファイルから作品を一括登録します。すべて<strong>クール作品</strong>として追加されます。</p>

                <div class="csv-import-guide" style="margin-top: 10px;">
                    <h4 class="csv-guide-title">CSVファイルの書式</h4>
                    <p>1行目にカラム名を指定し、2行目以降にデータを記述してください。</p>
                    <p>使用可能なカラム: <code>title</code>（必須）, <code>title_kana</code></p>

                    <div class="csv-examples">
                        <h4>例1: 作品名のみ</h4>
                        <pre class="csv-example-code">title
ONE PIECE
NARUTO</pre>

                        <h4>例2: 作品名 + かな</h4>
                        <pre class="csv-example-code">title,title_kana
ONE PIECE,わんぴーす
NARUTO,なると</pre>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label" for="animeCsvFile">CSVファイル</label>
                    <input type="file" id="animeCsvFile" accept=".csv" class="form-input-file">
                </div>

                <div id="animeCsvPreview" style="display:none; margin-top: 15px;">
                    <h4 class="csv-guide-title">プレビュー</h4>
                    <div class="table-responsive">
                        <table class="table" id="animeCsvPreviewTable">
                            <thead id="animeCsvPreviewHead"></thead>
                            <tbody id="animeCsvPreviewBody"></tbody>
                        </table>
                    </div>
                    <p class="csv-preview-count" id="animeCsvPreviewCount"></p>
                </div>
            </div>
            <div class="csv-modal-footer">
                <button type="button" class="btn btn-primary" id="animeCsvImportBtn" onclick="executeAnimeCsvImport()" disabled>インポート実行</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('csvImportModal').style.display='none'">キャンセル</button>
            </div>
        </div>
    </div>

    <script>
        let parsedAnimeCsvData = [];

        document.getElementById('animeCsvFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            parsedAnimeCsvData = [];
            document.getElementById('animeCsvImportBtn').disabled = true;

            if (!file) {
                document.getElementById('animeCsvPreview').style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                let text = event.target.result;
                // BOM除去
                if (text.charCodeAt(0) === 0xFEFF) text = text.slice(1);
                const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');

                if (lines.length < 2) {
                    alert('CSVファイルにはヘッダー行と少なくとも1行のデータが必要です。');
                    document.getElementById('animeCsvPreview').style.display = 'none';
                    return;
                }

                const headers = lines[0].split(',').map(h => h.trim());
                const allowedColumns = ['title', 'title_kana'];
                const displayNames = { 'title': '作品名', 'title_kana': '作品名（かな）' };

                const invalidHeaders = headers.filter(h => !allowedColumns.includes(h));
                if (invalidHeaders.length > 0) {
                    alert('不正なカラム名が含まれています: ' + invalidHeaders.join(', ') + '\n使用可能: title, title_kana');
                    return;
                }
                if (!headers.includes('title')) {
                    alert('titleカラムは必須です。');
                    return;
                }

                let headHtml = '<tr><th>#</th>';
                headers.forEach(h => { headHtml += '<th>' + (displayNames[h] || h) + '</th>'; });
                headHtml += '</tr>';
                document.getElementById('animeCsvPreviewHead').innerHTML = headHtml;

                let bodyHtml = '';
                parsedAnimeCsvData = [];
                for (let i = 1; i < lines.length; i++) {
                    const values = lines[i].split(',').map(v => v.trim());
                    const row = {};
                    headers.forEach((h, idx) => { row[h] = values[idx] || ''; });
                    parsedAnimeCsvData.push(row);

                    bodyHtml += '<tr><td>' + i + '</td>';
                    values.forEach(v => { bodyHtml += '<td>' + escapeHtml(v) + '</td>'; });
                    bodyHtml += '</tr>';
                }
                document.getElementById('animeCsvPreviewBody').innerHTML = bodyHtml;
                document.getElementById('animeCsvPreviewCount').textContent = parsedAnimeCsvData.length + ' 件の作品がインポートされます';
                document.getElementById('animeCsvPreview').style.display = 'block';
                document.getElementById('animeCsvImportBtn').disabled = false;
            };
            reader.readAsText(file, 'UTF-8');
        });

        function executeAnimeCsvImport() {
            if (parsedAnimeCsvData.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route("works.csv-import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ titles: parsedAnimeCsvData }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '{{ route("works.index") }}';
                } else {
                    alert('エラー: ' + (data.message || 'インポートに失敗しました。'));
                }
            })
            .catch(error => {
                alert('通信エラーが発生しました。');
                console.error(error);
            });
        }
    </script>
    @endif

    @if($animeTitle->exists)
    {{-- シリーズCSVインポートモーダル --}}
    <div id="seriesCsvImportModal" class="csv-modal-overlay" style="display:none;">
        <div class="csv-modal csv-modal-wide">
            <div class="csv-modal-header">
                <h3>シリーズCSVインポート</h3>
                <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="document.getElementById('seriesCsvImportModal').style.display='none'">✕</button>
            </div>
            <div class="csv-modal-body">
                <p>CSVファイルからシリーズとエピソードを一括登録します。</p>

                <div class="csv-import-guide" style="margin-top: 10px;">
                    <h4 class="csv-guide-title">CSVファイルの書式</h4>
                    <p>各シリーズは<strong>空行</strong>で区切ってください。</p>
                    <p>1行目: <code>シリーズ名,フォーマット</code>（シリーズ / スペシャル / 映画）</p>
                    <p>2行目: エピソードのヘッダー行（<code>episode_n</code>, <code>episode_title</code>, <code>onair_date</code>, <code>duration_min</code> の組み合わせ）</p>
                    <p>3行目以降: エピソードデータ</p>
                    <p><code>episode_n</code> は自由形式です（例: 01. / 第1話 / Episode01 / Ⅰ）。</p>

                    <div class="csv-examples">
                        <h4>例1: 話数＋サブタイトル</h4>
                        <pre class="csv-example-code">第1シリーズ,シリーズ
episode_n,episode_title
第1話,帰ってきた…
第2話,夢はひとつ

第2シリーズ,シリーズ
episode_n,episode_title
第1話,新たな冒険
第2話,出発の朝</pre>

                        <h4>例2: サブタイトル＋尺</h4>
                        <pre class="csv-example-code">第1シリーズ,シリーズ
episode_title,duration_min
帰ってきた…,24
夢はひとつ!,24

劇場版,映画
episode_title,duration_min
完結編,120</pre>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label" for="seriesCsvFile">CSVファイル</label>
                    <input type="file" id="seriesCsvFile" accept=".csv" class="form-input-file">
                </div>

                <div id="seriesCsvPreview" style="display:none; margin-top: 15px;">
                    <h4 class="csv-guide-title">プレビュー</h4>
                    <div id="seriesCsvPreviewContent"></div>
                    <p class="csv-preview-count" id="seriesCsvPreviewCount"></p>
                </div>
            </div>
            <div class="csv-modal-footer">
                <button type="button" class="btn btn-primary" id="seriesCsvImportBtn" onclick="executeSeriesCsvImport()" disabled>インポート実行</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('seriesCsvImportModal').style.display='none'">キャンセル</button>
            </div>
        </div>
    </div>

    <script>
        let parsedSeriesCsvData = [];
        const formatLabelMap = {
            'シリーズ': 'シリーズ',
            'スペシャル': 'スペシャル',
            '映画': '映画',
        };
        const allowedEpisodeColumns = ['episode_n', 'episode_title', 'onair_date', 'duration_min'];
        const episodeDisplayNames = {
            'episode_n': '話数',
            'episode_title': 'サブタイトル',
            'onair_date': '放送年',
            'duration_min': '尺（分）'
        };

        document.getElementById('seriesCsvFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            parsedSeriesCsvData = [];
            document.getElementById('seriesCsvImportBtn').disabled = true;

            if (!file) {
                document.getElementById('seriesCsvPreview').style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                let text = event.target.result;
                // BOM除去
                if (text.charCodeAt(0) === 0xFEFF) text = text.slice(1);

                try {
                    parsedSeriesCsvData = parseSeriesCsv(text);
                } catch (err) {
                    alert('CSVの解析エラー: ' + err.message);
                    document.getElementById('seriesCsvPreview').style.display = 'none';
                    return;
                }

                if (parsedSeriesCsvData.length === 0) {
                    alert('インポート可能なシリーズが見つかりませんでした。');
                    document.getElementById('seriesCsvPreview').style.display = 'none';
                    return;
                }

                // プレビュー描画
                let previewHtml = '';
                let totalEpisodes = 0;
                parsedSeriesCsvData.forEach((series, sIdx) => {
                    previewHtml += '<div class="series-csv-preview-block">';
                    previewHtml += '<h4 class="series-csv-preview-title">' + escapeHtml(series.name) + ' <span class="badge badge-active">' + escapeHtml(series.format_type) + '</span></h4>';
                    previewHtml += '<div class="table-responsive"><table class="table"><thead><tr><th>#</th>';
                    series.headers.forEach(h => {
                        previewHtml += '<th>' + (episodeDisplayNames[h] || h) + '</th>';
                    });
                    previewHtml += '</tr></thead><tbody>';
                    series.episodes.forEach((ep, eIdx) => {
                        previewHtml += '<tr><td>' + (eIdx + 1) + '</td>';
                        series.headers.forEach(h => {
                            const key = h === 'episode_n' ? 'episode_no' : h;
                            previewHtml += '<td>' + escapeHtml(String(ep[key] || '')) + '</td>';
                        });
                        previewHtml += '</tr>';
                        totalEpisodes++;
                    });
                    previewHtml += '</tbody></table></div></div>';
                });

                document.getElementById('seriesCsvPreviewContent').innerHTML = previewHtml;
                document.getElementById('seriesCsvPreviewCount').textContent =
                    parsedSeriesCsvData.length + ' シリーズ / ' + totalEpisodes + ' エピソードがインポートされます';
                document.getElementById('seriesCsvPreview').style.display = 'block';
                document.getElementById('seriesCsvImportBtn').disabled = false;
            };
            reader.readAsText(file, 'UTF-8');
        });

        function parseSeriesCsv(text) {
            const lines = text.split(/\r?\n/);
            const blocks = [];
            let currentBlock = [];

            // 空行でブロック分割
            for (const line of lines) {
                if (line.trim() === '') {
                    if (currentBlock.length > 0) {
                        blocks.push(currentBlock);
                        currentBlock = [];
                    }
                } else {
                    currentBlock.push(line);
                }
            }
            if (currentBlock.length > 0) {
                blocks.push(currentBlock);
            }

            const result = [];
            for (let bIdx = 0; bIdx < blocks.length; bIdx++) {
                const block = blocks[bIdx];
                if (block.length < 3) {
                    throw new Error('ブロック' + (bIdx + 1) + ': シリーズ行・ヘッダー行・データ行が必要です（最低3行）。');
                }

                // 1行目: シリーズ名,フォーマット
                const seriesLine = block[0].split(',').map(v => v.trim());
                if (seriesLine.length < 2) {
                    throw new Error('ブロック' + (bIdx + 1) + ' 1行目: 「シリーズ名,フォーマット」の形式で指定してください。');
                }
                const seriesName = seriesLine[0];
                const formatType = seriesLine[1];

                if (!formatLabelMap[formatType]) {
                    throw new Error('ブロック' + (bIdx + 1) + ': フォーマット「' + formatType + '」は無効です。使用可能: シリーズ, スペシャル, 映画');
                }

                // 2行目: ヘッダー
                const headers = block[1].split(',').map(h => h.trim());
                const invalidHeaders = headers.filter(h => !allowedEpisodeColumns.includes(h));
                if (invalidHeaders.length > 0) {
                    throw new Error('ブロック' + (bIdx + 1) + ': 不正なカラム名「' + invalidHeaders.join(', ') + '」。使用可能: ' + allowedEpisodeColumns.join(', '));
                }

                // 3行目以降: データ
                const episodes = [];
                for (let i = 2; i < block.length; i++) {
                    const values = block[i].split(',').map(v => v.trim());
                    if (values.length !== headers.length) {
                        throw new Error('ブロック' + (bIdx + 1) + ' ' + (i + 1) + '行目: カラム数がヘッダーと一致しません。');
                    }
                    const ep = {};
                    headers.forEach((h, idx) => {
                        if (h === 'episode_n') {
                            ep['episode_no'] = values[idx] || String(episodes.length + 1);
                        } else if (h === 'duration_min') {
                            ep['duration_min'] = parseInt(values[idx]) || null;
                        } else if (h === 'onair_date') {
                            ep['onair_date'] = parseInt(values[idx]) || null;
                        } else {
                            ep[h] = values[idx];
                        }
                    });
                    // episode_noがなければ連番
                    if (!ep.hasOwnProperty('episode_no')) {
                        ep['episode_no'] = String(episodes.length + 1);
                    }
                    episodes.push(ep);
                }

                result.push({
                    name: seriesName,
                    format_type: formatType,
                    headers: headers,
                    episodes: episodes,
                });
            }
            return result;
        }

        function executeSeriesCsvImport() {
            if (parsedSeriesCsvData.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // headersはバックエンド不要なので除去
            const payload = parsedSeriesCsvData.map(s => ({
                name: s.name,
                format_type: s.format_type,
                episodes: s.episodes,
            }));

            document.getElementById('seriesCsvImportBtn').disabled = true;

            fetch('{{ route("works.series-csv-import", $animeTitle) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ series: payload }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('エラー: ' + (data.message || 'インポートに失敗しました。'));
                    document.getElementById('seriesCsvImportBtn').disabled = false;
                }
            })
            .catch(error => {
                alert('通信エラーが発生しました。');
                console.error(error);
                document.getElementById('seriesCsvImportBtn').disabled = false;
            });
        }
    </script>
    @endif
@endsection
