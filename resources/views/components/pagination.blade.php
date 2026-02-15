@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-nav">
            {{-- 前へボタン --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn-disabled">&lt;前へ</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">&lt;前へ</a>
            @endif

            {{-- ページプルダウン --}}
            <select class="pagination-select" onchange="location.href=this.value">
                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    <option value="{{ $paginator->url($i) }}" {{ $i == $paginator->currentPage() ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>

            {{-- 次へボタン --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">次へ&gt;</a>
            @else
                <span class="pagination-btn pagination-btn-disabled">次へ&gt;</span>
            @endif
        </div>
        <div class="pagination-info">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}/{{ $paginator->total() }}
        </div>
    </div>
@endif
