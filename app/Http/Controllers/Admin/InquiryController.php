<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InquiryController extends Controller
{
    /** ステータスのラベル → 階層キー */
    private const STATUS_TONE = [
        '未対応' => 'pending',
        '受付中' => 'pending',
        '受付済' => 'pending',
        '対応中' => 'in_progress',
        '保留' => 'in_progress',
        '対応済' => 'resolved',
        '完了' => 'resolved',
        'クローズ' => 'closed',
        'クローズ済' => 'closed',
    ];

    public function index()
    {
        $inquiries = $this->fetchInquiries();

        $statusCounts = [
            'all' => count($inquiries),
            'pending' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];
        foreach ($inquiries as $inq) {
            $tone = $inq['status_tone'];
            if (isset($statusCounts[$tone])) {
                $statusCounts[$tone]++;
            }
        }

        return view('admin.inquiry.index', [
            'inquiries' => $inquiries,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show($id)
    {
        $inquiry = $this->fetchInquiry((int) $id);
        if (! $inquiry) {
            abort(404);
        }
        return view('admin.inquiry.show', [
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * 実データのテーブルを解決する。
     * 現行スキーマでは問い合わせは support_inquiries に保存されるため、
     * inquiries が無い環境では support_inquiries へフォールバックする
     * （旧: inquiries 固定参照だったため常に 0 件表示になるサイレント障害があった）。
     */
    private function resolveTable(): ?string
    {
        if (Schema::hasTable('inquiries')) {
            return 'inquiries';
        }
        if (Schema::hasTable('support_inquiries')) {
            return 'support_inquiries';
        }
        return null;
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchInquiries(): array
    {
        $table = $this->resolveTable();
        if ($table === null) {
            return [];
        }

        return DB::table($table)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($row) => $this->normalizeRow($row))
            ->all();
    }

    private function fetchInquiry(int $id): ?array
    {
        $table = $this->resolveTable();
        if ($table === null) {
            return null;
        }
        $row = DB::table($table)->where('id', $id)->first();
        if (! $row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    /** support_inquiries の英語ステータス → 表示ラベル / 階層キー */
    private const EN_STATUS = [
        'new'         => ['未対応', 'pending'],
        'in_progress' => ['対応中', 'in_progress'],
        'resolved'    => ['対応済', 'resolved'],
        'dismissed'   => ['クローズ', 'closed'],
    ];

    /** support_inquiries の category → 件名ラベル */
    private const CATEGORY_LABEL = [
        'account'  => 'アカウントについて',
        'feature'  => '機能について',
        'bug'      => '不具合の報告',
        'feedback' => 'ご意見・ご要望',
        'other'    => 'その他',
    ];

    /** DBレコードを画面表示用に正規化（inquiries / support_inquiries のカラム名のゆらぎを吸収） */
    private function normalizeRow(object $row): array
    {
        $statusRaw = trim((string) ($row->status ?? '未対応'));
        if (isset(self::EN_STATUS[$statusRaw])) {
            [$statusLabel, $statusTone] = self::EN_STATUS[$statusRaw];
        } else {
            $statusLabel = $statusRaw !== '' ? $statusRaw : '未対応';
            $statusTone = self::STATUS_TONE[$statusLabel] ?? 'pending';
        }
        $createdAt = isset($row->created_at) ? \Carbon\Carbon::parse($row->created_at) : now();

        $category = trim((string) ($row->category ?? ''));

        return [
            'id' => $row->id ?? null,
            'from_type' => trim((string) ($row->from_type ?? $row->sender_type ?? $row->type ?? '')),
            'from_name' => trim((string) ($row->from_name ?? $row->name ?? $row->user_name ?? $row->sender_id ?? '')),
            'from_email' => trim((string) ($row->from_email ?? $row->email ?? '')),
            'subject' => trim((string) ($row->subject ?? $row->title ?? (self::CATEGORY_LABEL[$category] ?? $category))),
            'body' => (string) ($row->body ?? $row->message ?? $row->content ?? ''),
            'status' => $statusLabel,
            'status_tone' => $statusTone,
            'created_at' => $createdAt,
            'updated_at' => isset($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at) : null,
        ];
    }
}
