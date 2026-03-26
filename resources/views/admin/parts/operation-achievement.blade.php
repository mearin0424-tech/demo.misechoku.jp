{{-- オペレーション各画面用：実績（累計件数）の説明 --}}
@php
    $key = $operationAchievementRoute ?? '';
    $counts = $adminOperationAchievements ?? [];
    $n = (int) ($counts[$key] ?? 0);
    $copy = [
        'admin.invoices.index' => [
            'title' => '請求書発行の実績',
            'body' => '請求書番号が登録済みの入金申請の累計件数です。',
        ],
        'admin.deposits.index' => [
            'title' => '入金・振込フローの実績',
            'body' => 'ステータスが「完了」となった案件（請求〜振込・確認まで終了）の累計件数です。',
        ],
        'admin.verification.index' => [
            'title' => '書類審査の実績',
            'body' => 'キャスト本人確認・店舗書類のうち、承認または却下まで完了した件数の累計です。',
        ],
        'admin.inquiries.index' => [
            'title' => '問合せ対応の実績',
            'body' => '対応済み・完了・クローズとした問合せの累計件数です。',
        ],
    ];
@endphp
@if($key !== '' && isset($copy[$key]))
    <section class="admin-panel" style="margin-bottom: 16px;">
        <h2 class="admin-panel-title">{{ $copy[$key]['title'] }}</h2>
        <p class="admin-description" style="margin: 0;">
            {{ $copy[$key]['body'] }}
            <strong style="color: var(--admin-gold); margin-left: 6px;">{{ number_format($n) }}件</strong>
        </p>
    </section>
@endif
