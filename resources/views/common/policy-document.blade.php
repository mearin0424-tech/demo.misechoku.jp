@extends('layouts.app')

@php
    use App\Support\MarkdownRenderer;
@endphp

@section('title', $document->title)
@section('header_title', $document->title)
@section('body-class', 'is-policy-document')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/policy-page.css') }}">
@endpush

@section('content')
    <div class="policy-doc-shell">
        @if ($document->lead_title || $document->lead_body)
            <section class="policy-doc-lead">
                @if ($document->lead_title)
                    <p class="policy-doc-lead-title">{{ $document->lead_title }}</p>
                @endif
                @if ($document->lead_body)
                    <div class="policy-md">
                        {!! MarkdownRenderer::toHtml($document->lead_body) !!}
                    </div>
                @endif
            </section>
        @endif

        @if ($document->isAbout() && is_array($document->meta))
            @php
                $metaRows = collect($metaSchema)->filter(function (array $row) use ($document) {
                    $entry = $document->meta[$row['key']] ?? null;
                    $value = is_array($entry) ? ($entry['value'] ?? '') : '';

                    return trim((string) $value) !== '';
                });
            @endphp
            @if ($metaRows->isNotEmpty())
                <section class="policy-doc-meta">
                    @foreach ($metaRows as $row)
                        @php
                            $entry = $document->meta[$row['key']] ?? null;
                            $value = is_array($entry) ? ($entry['value'] ?? '') : '';
                            $label = is_array($entry) ? ($entry['label'] ?? $row['label']) : $row['label'];
                        @endphp
                        <div class="policy-doc-meta-row">
                            <div class="policy-doc-meta-label">{{ $label }}</div>
                            <div class="policy-md">
                                {!! MarkdownRenderer::toHtml($value) !!}
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif
        @endif

        @forelse ($document->chapters as $chapter)
            <article class="policy-doc-chapter">
                <h2 class="policy-doc-chapter-title">{{ $chapter->title }}</h2>
                <div class="policy-md">
                    {!! MarkdownRenderer::toHtml($chapter->body) !!}
                </div>
            </article>
        @empty
            <p style="color: var(--policy-text-muted, #5c524d); font-size: 0.88rem;">本文は準備中です。</p>
        @endforelse
    </div>
@endsection
