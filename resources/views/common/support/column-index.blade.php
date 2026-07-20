@extends('layouts.app-v2')

@section('title', 'お役立ちコラム')

@php
    $showRoute = $isGuest
        ? 'pages.support.column.show'
        : ($isCast ? 'cast.column.show' : 'shop.column.show');
    $availableTags = $availableTags ?? [];
    $activeTag = $activeTag ?? '';
@endphp

@section('content')
<div class="col-list-page">
    {{-- タイトルはヘッダー中央、説明はオコジョガイド（character_guide_settings）に集約 --}}

    {{-- タグ絞り込みチップ（横スクロール1行） --}}
    @if(!empty($availableTags))
        <nav class="col-tag-bar" aria-label="タグで絞り込み">
            <a href="{{ request()->url() }}"
               class="col-tag-chip {{ $activeTag === '' ? 'is-active' : '' }}">すべて</a>
            @foreach($availableTags as $tag)
                <a href="{{ request()->url() }}?tag={{ urlencode($tag) }}"
                   class="col-tag-chip {{ $activeTag === $tag ? 'is-active' : '' }}">
                    <i class="fas fa-tag" aria-hidden="true"></i>{{ $tag }}
                </a>
            @endforeach
        </nav>
    @endif

    @if($activeTag !== '')
        <p class="col-filter-note">
            <i class="fas fa-filter" aria-hidden="true"></i>
            「{{ $activeTag }}」のコラム {{ $articles->total() }} 件
            <a href="{{ request()->url() }}" class="col-filter-note__clear"><i class="fas fa-times-circle"></i> 解除</a>
        </p>
    @endif

    {{-- 記事一覧 --}}
    <div class="col-article-list">
        @forelse($articles as $article)
            @php
                $cat = $article->columnCategory;
                $excerpt = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $article->body))), 72);
                $dateLabel = $article->published_at?->format('Y.m.d') ?? $article->updated_at->format('Y.m.d');
                $thumb = $article->image_path ? asset(ltrim($article->image_path, '/')) : null;
                $articleTags = is_array($article->tags) ? $article->tags : [];
            @endphp
            <article class="col-article-card">
                <a href="{{ route($showRoute, $article->slug) }}" class="col-article-card__link">
                    {{-- サムネイル（未登録時はカテゴリ頭文字のプレースホルダー） --}}
                    <div class="col-article-card__thumb">
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="" loading="lazy" decoding="async">
                        @else
                            <span class="col-article-card__thumb-ph" aria-hidden="true">
                                <i class="fas fa-lightbulb"></i>
                            </span>
                        @endif
                    </div>
                    <div class="col-article-card__body">
                        <div class="col-article-card__meta">
                            @if($cat)
                                <span class="col-article-card__cat">{{ $cat->name }}</span>
                            @endif
                            <time class="col-article-card__date" datetime="{{ $article->published_at?->toDateString() }}">{{ $dateLabel }}</time>
                        </div>
                        <h2 class="col-article-card__title">{{ $article->title }}</h2>
                        <p class="col-article-card__excerpt">{{ $excerpt }}</p>
                        @if(!empty($articleTags))
                            <div class="col-article-card__tags">
                                @foreach(array_slice($articleTags, 0, 4) as $t)
                                    <span class="col-article-card__tag">#{{ $t }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <span class="col-article-card__chev" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </a>
            </article>
        @empty
            <p class="col-article-empty">
                @if($activeTag !== '')
                    このタグのコラムはまだありません。
                @else
                    表示できるコラムがありません。
                @endif
            </p>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div class="col-article-pagination">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ============================================================
   お役立ちコラム：記事一覧UI（ライトモード基調）
   ============================================================ */
.col-list-page {
    padding: 14px 16px 32px;
    color: #4b465c;
}
@media (min-width: 768px) {
    .col-list-page { padding: 20px 24px 40px; }
}

/* --- タグチップ（横スクロール1行） --- */
.col-tag-bar {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    margin: 0 -16px 14px;
    padding: 2px 16px 6px;
}
.col-tag-bar::-webkit-scrollbar { display: none; }
.col-tag-chip {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid rgba(124, 58, 237, 0.24);
    color: #574d6f;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(76, 29, 149, 0.06);
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.col-tag-chip i { font-size: 0.62rem; color: #a78bfa; }
.col-tag-chip:hover { border-color: rgba(124, 58, 237, 0.45); }
.col-tag-chip.is-active {
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 3px 10px rgba(124, 58, 237, 0.30);
}
.col-tag-chip.is-active i { color: rgba(255, 255, 255, 0.85); }

.col-filter-note {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 12px;
    font-size: 0.78rem;
    color: #574d6f;
}
.col-filter-note i { color: #7c3aed; }
.col-filter-note__clear {
    margin-left: auto;
    color: #6d28d9;
    font-weight: 700;
    text-decoration: none;
}

/* --- 記事カード --- */
.col-article-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.col-article-card {
    background: #ffffff;
    border: 1px solid rgba(124, 58, 237, 0.16);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(76, 29, 149, 0.07);
    transition: border-color 0.15s ease, transform 0.12s ease;
}
.col-article-card:active { transform: scale(0.99); }
.col-article-card:hover { border-color: rgba(124, 58, 237, 0.38); }
.col-article-card__link {
    display: flex;
    align-items: stretch;
    gap: 12px;
    padding: 12px;
    color: inherit;
    text-decoration: none;
}
.col-article-card__thumb {
    flex: 0 0 84px;
    width: 84px;
    height: 84px;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #ede7f8, #e0d5f5);
    align-self: center;
}
.col-article-card__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.col-article-card__thumb-ph {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #a78bfa;
}
.col-article-card__body {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    justify-content: center;
}
.col-article-card__meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.68rem;
}
.col-article-card__cat {
    display: inline-flex;
    padding: 2px 9px;
    border-radius: 999px;
    background: rgba(124, 58, 237, 0.09);
    color: #6d28d9;
    font-weight: 800;
    letter-spacing: 0.03em;
}
.col-article-card__date {
    color: #857ca0;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}
.col-article-card__title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 800;
    line-height: 1.45;
    color: #4b465c;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.col-article-card__excerpt {
    margin: 0;
    font-size: 0.76rem;
    line-height: 1.6;
    color: #574d6f;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.col-article-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 8px;
    margin-top: 2px;
}
.col-article-card__tag {
    font-size: 0.66rem;
    font-weight: 700;
    color: #7c3aed;
}
.col-article-card__chev {
    flex: 0 0 auto;
    align-self: center;
    color: rgba(124, 58, 237, 0.40);
    font-size: 0.8rem;
}

.col-article-empty {
    font-size: 0.88rem;
    color: #574d6f;
    text-align: center;
    padding: 32px 8px;
    background: #ffffff;
    border: 1px dashed rgba(124, 58, 237, 0.28);
    border-radius: 14px;
}

/* --- ページネーション --- */
.col-article-pagination { margin-top: 20px; }
.col-article-pagination .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 0;
}
.col-article-pagination .pagination li span,
.col-article-pagination .pagination li a {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 10px;
    border: 1px solid rgba(124, 58, 237, 0.24);
    background: #ffffff;
    color: #574d6f;
    font-size: 0.85rem;
    text-decoration: none;
}
.col-article-pagination .pagination li.active span {
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    border-color: transparent;
    color: #ffffff;
}

/* --- ダークモード（テーマトグルで theme-light が外れた場合）の可読性 --- */
body:not(.theme-light) .col-list-page { color: #f5f5f5; }
body:not(.theme-light) .col-tag-chip {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(168, 85, 247, 0.35);
    color: #d4cce6;
}
body:not(.theme-light) .col-article-card {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(168, 85, 247, 0.30);
}
body:not(.theme-light) .col-article-card__title { color: #f5f5f5; }
body:not(.theme-light) .col-article-card__excerpt { color: #b8b8b8; }
body:not(.theme-light) .col-article-card__date { color: #8b84a1; }
body:not(.theme-light) .col-article-empty {
    background: rgba(255, 255, 255, 0.04);
    color: #b8b8b8;
}
body:not(.theme-light) .col-filter-note { color: #b8b8b8; }
body:not(.theme-light) .col-article-pagination .pagination li span,
body:not(.theme-light) .col-article-pagination .pagination li a {
    background: rgba(255, 255, 255, 0.06);
    color: #d4cce6;
}
</style>
@endpush
