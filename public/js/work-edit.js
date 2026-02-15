/**
 * 作品編集画面 JavaScript
 * - シリーズ / エピソードの動的追加・削除
 * - ドラッグ&ドロップによる並び替え
 */

document.addEventListener('DOMContentLoaded', () => {
    initSeriesDragDrop();
    initAllEpisodeDragDrop();
});

// ========================================
// グローバルカウンター（name属性のインデックス採番用）
// ========================================
let seriesCounter = document.querySelectorAll('.series-edit-block').length;

function getNextEpisodeIndex(seriesIndex) {
    const list = document.querySelector(`.episode-edit-list[data-series-index="${seriesIndex}"]`);
    return list ? list.querySelectorAll('.episode-edit-row').length : 0;
}

function getNextPlatformIndex(seriesIndex) {
    const list = document.querySelector(`.platform-edit-list[data-series-index="${seriesIndex}"]`);
    return list ? list.querySelectorAll('.platform-edit-row').length : 0;
}

function getNextArcIndex(seriesIndex) {
    const list = document.querySelector(`.arc-edit-list[data-series-index="${seriesIndex}"]`);
    return list ? list.querySelectorAll('.arc-edit-row').length : 0;
}

// ========================================
// シリーズ追加
// ========================================
function addSeries() {
    const idx = seriesCounter++;
    const seriesOrder = idx + 1;

    const formatOptions = formatTypeOptions.map(f =>
        `<option value="${f.value}">${f.label}</option>`
    ).join('');

    const html = `
        <div class="series-edit-block collapsed" data-series-index="${idx}" draggable="true">
            <input type="hidden" name="series[${idx}][id]" value="">
            <input type="hidden" name="series[${idx}][series_order]" value="${seriesOrder}" class="series-order-input">

            <div class="series-edit-header">
                <span class="drag-handle series-drag" title="ドラッグして並び替え">☰</span>
                <span class="toggle-icon collapsible" onclick="toggleEditSeries(this)" title="折りたたみ">▶</span>
                <div class="series-edit-fields">
                    <div class="edit-form-group flex-2">
                        <label class="form-label">シリーズ名 <span class="required">*</span></label>
                        <input type="text" name="series[${idx}][name]" class="form-control" value="" placeholder="シリーズ名">
                    </div>
                    <div class="edit-form-group flex-1">
                        <label class="form-label">フォーマット <span class="required">*</span></label>
                        <select name="series[${idx}][format_type]" class="form-control">
                            ${formatOptions}
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removeSeries(this)" title="シリーズ削除">✕</button>
            </div>

            <div class="subsection series-collapsible-content">
                <div class="subsection-header">
                    <h4 class="subsection-title">配信プラットフォーム</h4>
                    <button type="button" class="btn btn-outline btn-xs" onclick="addPlatform(this, ${idx})">＋ 追加</button>
                </div>
                <div class="platform-edit-list" data-series-index="${idx}"></div>
            </div>

            <div class="subsection series-collapsible-content">
                <div class="subsection-header">
                    <h4 class="subsection-title">エピソード</h4>
                    <button type="button" class="btn btn-outline btn-xs" onclick="addEpisode(this, ${idx})">＋ 追加</button>
                </div>
                <div class="episode-edit-list" data-series-index="${idx}"></div>
            </div>

            <div class="subsection series-collapsible-content">
                <div class="subsection-header">
                    <h4 class="subsection-title">アーク（編）</h4>
                    <button type="button" class="btn btn-outline btn-xs" onclick="addArc(this, ${idx})">＋ 追加</button>
                </div>
                <div class="arc-edit-list" data-series-index="${idx}"></div>
            </div>
        </div>
    `;

    document.getElementById('seriesList').insertAdjacentHTML('beforeend', html);

    const newBlock = document.querySelector(`.series-edit-block[data-series-index="${idx}"]`);
    attachSeriesDragEvents(newBlock);
    updateSeriesOrders();
}

// ========================================
// シリーズ削除
// ========================================
function removeSeries(btn) {
    const block = btn.closest('.series-edit-block');
    const idInput = block.querySelector('input[name$="[id]"]');
    const id = idInput ? idInput.value : '';

    if (!confirm('このシリーズとその全エピソード・アークを削除しますか？')) return;

    if (id) {
        const deletedIds = document.getElementById('deletedIds');
        deletedIds.insertAdjacentHTML('beforeend',
            `<input type="hidden" name="deleted_series_ids[]" value="${id}">`
        );
    }

    block.remove();
    updateSeriesOrders();
}

// ========================================
// 配信PF追加
// ========================================
function addPlatform(btn, seriesIndex) {
    const list = btn.closest('.subsection').querySelector('.platform-edit-list');
    const idx = getNextPlatformIndex(seriesIndex);

    const pfOptions = platformOptions.map(p =>
        `<option value="${p.id}">${p.name}</option>`
    ).join('');

    const wcOptions = watchConditionOptions.map(w =>
        `<option value="${w.value}">${w.label}</option>`
    ).join('');

    const html = `
        <div class="platform-edit-row">
            <select name="series[${seriesIndex}][platforms][${idx}][platform_id]" class="form-control form-control-sm">
                ${pfOptions}
            </select>
            <select name="series[${seriesIndex}][platforms][${idx}][watch_condition]" class="form-control form-control-sm">
                ${wcOptions}
            </select>
            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="this.closest('.platform-edit-row').remove()">✕</button>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);
}

// ========================================
// エピソード追加
// ========================================
function addEpisode(btn, seriesIndex) {
    const list = btn.closest('.subsection').querySelector('.episode-edit-list');
    const idx = getNextEpisodeIndex(seriesIndex);

    // ヘッダー行がなければ追加
    if (!list.querySelector('.episode-edit-header-row')) {
        list.insertAdjacentHTML('afterbegin', `
            <div class="episode-edit-header-row">
                <span class="ep-col-handle"></span>
                <span class="ep-col-no">話数</span>
                <span class="ep-col-title">サブタイトル</span>
                <span class="ep-col-year">放送年</span>
                <span class="ep-col-dur">尺(分)</span>
                <span class="ep-col-action"></span>
            </div>
        `);
    }

    // 次の話数を自動算出
    const rows = list.querySelectorAll('.episode-edit-row');
    let nextNo = 1;
    if (rows.length > 0) {
        const lastInput = rows[rows.length - 1].querySelector('.episode-no-input');
        if (lastInput && lastInput.value) {
            nextNo = parseInt(lastInput.value) + 1;
        }
    }

    const html = `
        <div class="episode-edit-row" draggable="true">
            <input type="hidden" name="series[${seriesIndex}][episodes][${idx}][id]" value="">
            <span class="drag-handle episode-drag" title="ドラッグして並び替え">☰</span>
            <input type="number" name="series[${seriesIndex}][episodes][${idx}][episode_no]" class="form-control form-control-sm ep-col-no episode-no-input"
                   value="${nextNo}" min="1">
            <input type="text" name="series[${seriesIndex}][episodes][${idx}][episode_title]" class="form-control form-control-sm ep-col-title"
                   placeholder="サブタイトル">
            <input type="number" name="series[${seriesIndex}][episodes][${idx}][onair_date]" class="form-control form-control-sm ep-col-year"
                   placeholder="年">
            <input type="number" name="series[${seriesIndex}][episodes][${idx}][duration_min]" class="form-control form-control-sm ep-col-dur"
                   value="24" min="1">
            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="removeEpisode(this)">✕</button>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);

    const newRow = list.querySelector('.episode-edit-row:last-child');
    attachEpisodeDragEvents(newRow, list);
}

// ========================================
// エピソード削除
// ========================================
function removeEpisode(btn) {
    const row = btn.closest('.episode-edit-row');
    const idInput = row.querySelector('input[name$="[id]"]');
    const id = idInput ? idInput.value : '';

    if (id) {
        const deletedIds = document.getElementById('deletedIds');
        deletedIds.insertAdjacentHTML('beforeend',
            `<input type="hidden" name="deleted_episode_ids[]" value="${id}">`
        );
    }

    const list = row.closest('.episode-edit-list');
    row.remove();

    // ヘッダー行だけ残っている場合は削除
    if (list.querySelectorAll('.episode-edit-row').length === 0) {
        const header = list.querySelector('.episode-edit-header-row');
        if (header) header.remove();
    }
}

// ========================================
// アーク追加
// ========================================
function addArc(btn, seriesIndex) {
    const list = btn.closest('.subsection').querySelector('.arc-edit-list');
    const idx = getNextArcIndex(seriesIndex);

    const html = `
        <div class="arc-edit-row">
            <input type="hidden" name="series[${seriesIndex}][arcs][${idx}][id]" value="">
            <input type="text" name="series[${seriesIndex}][arcs][${idx}][name]" class="form-control form-control-sm arc-col-name"
                   placeholder="アーク名">
            <input type="number" name="series[${seriesIndex}][arcs][${idx}][start_episode_no]" class="form-control form-control-sm arc-col-ep"
                   placeholder="開始" min="1">
            <span class="arc-separator">〜</span>
            <input type="number" name="series[${seriesIndex}][arcs][${idx}][end_episode_no]" class="form-control form-control-sm arc-col-ep"
                   placeholder="終了" min="1">
            <button type="button" class="btn btn-danger btn-xs btn-icon" onclick="removeArc(this)">✕</button>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);
}

// ========================================
// アーク削除
// ========================================
function removeArc(btn) {
    const row = btn.closest('.arc-edit-row');
    const idInput = row.querySelector('input[name$="[id]"]');
    const id = idInput ? idInput.value : '';

    if (id) {
        const deletedIds = document.getElementById('deletedIds');
        deletedIds.insertAdjacentHTML('beforeend',
            `<input type="hidden" name="deleted_arc_ids[]" value="${id}">`
        );
    }

    row.remove();
}

// ========================================
// シリーズのドラッグ&ドロップ
// ========================================
let draggedSeries = null;

function initSeriesDragDrop() {
    document.querySelectorAll('.series-edit-block').forEach(block => {
        attachSeriesDragEvents(block);
    });
}

function attachSeriesDragEvents(block) {
    block.addEventListener('dragstart', (e) => {
        // ドラッグハンドルからのみ開始を許可
        if (!e.target.closest('.series-drag') && e.target !== block) {
            // エピソード行のドラッグかチェック
            if (e.target.closest('.episode-edit-row')) return;
            e.preventDefault();
            return;
        }
        draggedSeries = block;
        block.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', 'series');
    });

    block.addEventListener('dragend', () => {
        block.classList.remove('dragging');
        document.querySelectorAll('.series-edit-block').forEach(b => b.classList.remove('drag-over'));
        draggedSeries = null;
        updateSeriesOrders();
    });

    block.addEventListener('dragover', (e) => {
        if (!draggedSeries || draggedSeries === block) return;
        if (e.dataTransfer.types.includes('text/plain')) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            block.classList.add('drag-over');
        }
    });

    block.addEventListener('dragleave', () => {
        block.classList.remove('drag-over');
    });

    block.addEventListener('drop', (e) => {
        e.preventDefault();
        block.classList.remove('drag-over');
        if (!draggedSeries || draggedSeries === block) return;

        const list = document.getElementById('seriesList');
        const blocks = [...list.querySelectorAll('.series-edit-block')];
        const fromIdx = blocks.indexOf(draggedSeries);
        const toIdx = blocks.indexOf(block);

        if (fromIdx < toIdx) {
            block.after(draggedSeries);
        } else {
            block.before(draggedSeries);
        }
    });
}

function updateSeriesOrders() {
    const blocks = document.querySelectorAll('#seriesList .series-edit-block');
    blocks.forEach((block, index) => {
        const orderInput = block.querySelector('.series-order-input');
        if (orderInput) {
            orderInput.value = index + 1;
        }
    });
}

// ========================================
// エピソードのドラッグ&ドロップ
// ========================================
function initAllEpisodeDragDrop() {
    document.querySelectorAll('.episode-edit-list').forEach(list => {
        list.querySelectorAll('.episode-edit-row').forEach(row => {
            attachEpisodeDragEvents(row, list);
        });
    });
}

let draggedEpisode = null;

function attachEpisodeDragEvents(row, list) {
    row.addEventListener('dragstart', (e) => {
        if (!e.target.closest('.episode-drag')) {
            e.preventDefault();
            return;
        }
        e.stopPropagation();
        draggedEpisode = row;
        row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', 'episode');
    });

    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        list.querySelectorAll('.episode-edit-row').forEach(r => r.classList.remove('drag-over'));
        draggedEpisode = null;
        updateEpisodeNumbers(list);
    });

    row.addEventListener('dragover', (e) => {
        if (!draggedEpisode || draggedEpisode === row) return;
        e.preventDefault();
        e.stopPropagation();
        e.dataTransfer.dropEffect = 'move';
        row.classList.add('drag-over');
    });

    row.addEventListener('dragleave', () => {
        row.classList.remove('drag-over');
    });

    row.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        row.classList.remove('drag-over');
        if (!draggedEpisode || draggedEpisode === row) return;

        // 同じリスト内でのみ移動可能
        if (draggedEpisode.closest('.episode-edit-list') !== row.closest('.episode-edit-list')) return;

        const rows = [...list.querySelectorAll('.episode-edit-row')];
        const fromIdx = rows.indexOf(draggedEpisode);
        const toIdx = rows.indexOf(row);

        if (fromIdx < toIdx) {
            row.after(draggedEpisode);
        } else {
            row.before(draggedEpisode);
        }
    });
}

function updateEpisodeNumbers(list) {
    const rows = list.querySelectorAll('.episode-edit-row');
    rows.forEach((row, index) => {
        const noInput = row.querySelector('.episode-no-input');
        if (noInput) {
            noInput.value = index + 1;
        }
    });
}

// ========================================
// シリーズ折りたたみ
// ========================================
function toggleEditSeries(icon) {
    const block = icon.closest('.series-edit-block');
    block.classList.toggle('collapsed');

    if (block.classList.contains('collapsed')) {
        icon.textContent = '▶';
    } else {
        icon.textContent = '▼';
    }
}
