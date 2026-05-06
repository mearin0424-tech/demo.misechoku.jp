@php
    $documents = $documents ?? [];
@endphp

<div class="shop-mypage-section document-section">
    <h3 class="shop-mypage-section-label">Licenses</h3>
    @foreach($documents as $doc)
        @php
            $s = $doc['status'] ?? 'not_submitted';
            $record = $doc['record'] ?? null;
            $label = $doc['status_label'] ?? ($s === 'approved' ? '承認済み' : ($s === 'rejected' ? '差し戻し' : ($s === 'pending' ? '審査中' : '未提出')));
            $isMissing = $s === 'not_submitted';
        @endphp
        <a href="{{ route('shop.mypage.documents.manage', ['type' => $doc['key']]) }}"
            class="shop-mypage-license-card {{ $isMissing ? 'is-missing' : '' }}">
            <div class="shop-mypage-license-card-body">
                <p class="document-upload-name">{{ $doc['name'] }}</p>
                <div class="document-status-row">
                    <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}">{{ $label }}</span>
                    @if(!empty($record['expiring_soon']))
                        <span class="document-expiring-soon-chip">{{ $record['expiration_notice_label'] ?? '更新期限半年以内' }}</span>
                    @endif
                </div>
                <p class="document-upload-meta">
                    @if($isMissing)
                        タップしてファイルを提出してください
                    @else
                        最終更新: {{ $record['updated_at_label'] ?? '—' }}
                    @endif
                </p>
            </div>
        </a>
    @endforeach
</div>
