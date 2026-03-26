<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use Illuminate\Support\Facades\DB;

class CastController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService
    ) {
    }

    /**
     * キャスト管理一覧
     */
    public function index()
    {
        $operationSummaries = $this->billingManagementService->getOperationSummaryByEntity('cast');

        $casts = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->leftJoin('cast_identity_documents', 'casts.id', '=', 'cast_identity_documents.cast_id')
            ->select(
                'casts.id',
                'casts.created_at',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_identity_documents.status as identity_document_status'
            )
            ->orderByDesc('casts.created_at')
            ->get()
            ->map(function ($cast) use ($operationSummaries) {
                $castId = (string) $cast->id;
                $summary = $operationSummaries[$castId] ?? null;

                return [
                    'id' => $cast->id,
                    'name' => $cast->nickname ?: ($cast->name ?: '未設定'),
                    'fee' => 0,
                    'published_at' => $cast->created_at,
                    'identity_status' => ((int) ($cast->identity_document_status ?? 0)) === 2 ? '確認済み' : '未確認',
                    'operation_summary' => $summary,
                ];
            });

        return view('admin.casts.index', [
            'casts' => $casts,
        ]);
    }
}

