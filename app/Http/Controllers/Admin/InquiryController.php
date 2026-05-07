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

    /** @return array<int, array<string, mixed>> */
    private function fetchInquiries(): array
    {
        if (! Schema::hasTable('inquiries')) {
            return [];
        }

        return DB::table('inquiries')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($row) => $this->normalizeRow($row))
            ->all();
    }

    private function fetchInquiry(int $id): ?array
    {
        if (! Schema::hasTable('inquiries')) {
            return null;
        }
        $row = DB::table('inquiries')->where('id', $id)->first();
        if (! $row) {
            return null;
        }
        return $this->normalizeRow($row);
    }

    /** DBレコードを画面表示用に正規化（カラム名のゆらぎを吸収） */
    private function normalizeRow(object $row): array
    {
        $statusLabel = trim((string) ($row->status ?? '未対応'));
        $statusTone = self::STATUS_TONE[$statusLabel] ?? 'pending';
        $createdAt = isset($row->created_at) ? \Carbon\Carbon::parse($row->created_at) : now();

        return [
            'id' => $row->id ?? null,
            'from_type' => trim((string) ($row->from_type ?? $row->type ?? '')),
            'from_name' => trim((string) ($row->from_name ?? $row->name ?? $row->user_name ?? '')),
            'from_email' => trim((string) ($row->from_email ?? $row->email ?? '')),
            'subject' => trim((string) ($row->subject ?? $row->title ?? '')),
            'body' => (string) ($row->body ?? $row->message ?? $row->content ?? ''),
            'status' => $statusLabel,
            'status_tone' => $statusTone,
            'created_at' => $createdAt,
            'updated_at' => isset($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at) : null,
        ];
    }
}
