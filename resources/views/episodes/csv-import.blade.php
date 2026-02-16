@extends('layouts.app')

@section('title', 'エピソードCSVインポート - アニメ管理システム')

@section('content')
    <h1 class="page-title">エピソードCSVインポート</h1>

    <div class="detail-container">
        <div class="csv-import-info">
            <p class="csv-import-target">
                対象シリーズ: <strong>{{ $series->animeTitle->title }}</strong> ＞ <strong>{{ $series->name }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('episodes.csv-import', $series) }}" enctype="multipart/form-data" class="csv-import-form">
            @csrf

            <div class="form-group">
                <label for="csv_file" class="form-label">CSVファイル</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-input-file" required>
                @error('csv_file')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="csv-import-guide">
                <h3 class="csv-guide-title">CSVファイルの書式</h3>
                <p>1行目にカラム名を指定し、2行目以降にデータを記述してください。</p>
                <p>使用可能なカラム: <code>episode_n</code>, <code>episode_title</code>, <code>onair_date</code>, <code>duration_min</code></p>
                <p>カラムは任意の組み合わせで指定できます（最低1つ）。</p>
                <p><code>episode_n</code> を省略した場合、行の順番で自動的に連番（1, 2, 3...）が割り振られます。</p>
                <p><code>episode_n</code> は自由形式です（例: 01. / 第1話 / Episode01 / Ⅰ）。</p>

                <div class="csv-examples">
                    <h4>例1: エピソード番号とタイトル</h4>
                    <pre class="csv-example-code">episode_n,episode_title
第1話,帰ってきた…
第2話,夢はひとつ!</pre>

                    <h4>例2: タイトルと尺</h4>
                    <pre class="csv-example-code">episode_title,duration_min
帰ってきた…,24
夢はひとつ!,24</pre>

                    <h4>例3: 全カラム指定</h4>
                    <pre class="csv-example-code">episode_n,episode_title,onair_date,duration_min
第1話,帰ってきた…,2025,24
第2話,夢はひとつ!,2025,24</pre>
                </div>
            </div>

            <div id="csv-preview-area" class="csv-preview-area" style="display:none;">
                <h3 class="csv-guide-title">プレビュー</h3>
                <div class="table-responsive">
                    <table class="table" id="csv-preview-table">
                        <thead id="csv-preview-head"></thead>
                        <tbody id="csv-preview-body"></tbody>
                    </table>
                </div>
                <p class="csv-preview-count" id="csv-preview-count"></p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="csv-submit-btn">インポート実行</button>
                <a href="{{ route('works.show', $series->anime_title_id) }}" class="btn btn-secondary">戻る</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('csv_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                document.getElementById('csv-preview-area').style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const text = event.target.result;
                const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');

                if (lines.length < 2) {
                    document.getElementById('csv-preview-area').style.display = 'none';
                    return;
                }

                const headers = lines[0].split(',').map(h => h.trim());
                const allowedColumns = ['episode_n', 'episode_title', 'onair_date', 'duration_min'];
                const displayNames = {
                    'episode_n': 'エピソード番号',
                    'episode_title': 'サブタイトル',
                    'onair_date': '放送年',
                    'duration_min': '尺（分）'
                };

                // ヘッダー検証
                const invalidHeaders = headers.filter(h => !allowedColumns.includes(h));
                if (invalidHeaders.length > 0) {
                    alert('不正なカラム名が含まれています: ' + invalidHeaders.join(', '));
                    return;
                }

                // ヘッダー構築
                let headHtml = '<tr>';
                headHtml += '<th>#</th>';
                headers.forEach(h => {
                    headHtml += '<th>' + (displayNames[h] || h) + '</th>';
                });
                headHtml += '</tr>';
                document.getElementById('csv-preview-head').innerHTML = headHtml;

                // ボディ構築
                let bodyHtml = '';
                for (let i = 1; i < lines.length; i++) {
                    const values = lines[i].split(',').map(v => v.trim());
                    bodyHtml += '<tr>';
                    bodyHtml += '<td>' + i + '</td>';
                    values.forEach(v => {
                        bodyHtml += '<td>' + escapeHtml(v) + '</td>';
                    });
                    bodyHtml += '</tr>';
                }
                document.getElementById('csv-preview-body').innerHTML = bodyHtml;
                document.getElementById('csv-preview-count').textContent = (lines.length - 1) + ' 件のエピソードがインポートされます';
                document.getElementById('csv-preview-area').style.display = 'block';
            };
            reader.readAsText(file, 'UTF-8');
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    </script>
@endsection
