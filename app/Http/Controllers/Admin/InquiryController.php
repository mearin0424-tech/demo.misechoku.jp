<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InquiryController extends Controller
{
    public function index()
    {
        return view('admin.inquiry.index', [
            'inquiries' => $this->fetchInquiries(),
        ]);
    }

    /**
     * @return array<int, array{id: mixed, from_type: string, from_name: string, subject: string, status: string, created_at: \Carbon\Carbon}>
     */
    private function fetchInquiries(): array
    {
        if (!Schema::hasTable('inquiries')) {
            return [];
        }

        $rows = DB::table('inquiries')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return $rows->map(function ($row) {
            $fromType = trim((string) ($row->from_type ?? $row->type ?? ''));
            $fromName = trim((string) ($row->from_name ?? $row->name ?? $row->user_name ?? ''));
            $subject = trim((string) ($row->subject ?? $row->title ?? ''));
            $status = trim((string) ($row->status ?? '未対応'));

            return [
                'id' => $row->id ?? '',
                'from_type' => $fromType,
                'from_name' => $fromName,
                'subject' => $subject,
                'status' => $status,
                'created_at' => \Carbon\Carbon::parse($row->created_at ?? now()),
            ];
        })->all();
    }
}

