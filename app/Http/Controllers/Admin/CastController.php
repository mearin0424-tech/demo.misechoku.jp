<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminOperationLogService;
use App\Services\AdminPrivateAccessService;
use App\Services\BillingManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CastController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly AdminPrivateAccessService $privateAccessService,
        private readonly AdminOperationLogService $opLog,
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
                'casts.last_login_at',
                'casts.status as account_status',
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
                    'registered_at' => $cast->created_at,
                    'last_login_at' => $cast->last_login_at,
                    'account_status' => (int) ($cast->account_status ?? 0),
                    'identity_status' => ((int) ($cast->identity_document_status ?? 0)) === 2 ? '確認済み' : '未確認',
                    'operation_summary' => $summary,
                ];
            });

        return view('admin.casts.index', [
            'casts' => $casts,
        ]);
    }

    /**
     * キャスト詳細（公開情報＋運用実績は常時表示、非公開情報はパスワード解除制）
     */
    public function show(string $castId)
    {
        $cast = DB::table('casts')->where('id', $castId)->first();
        abort_unless($cast, 404);

        $profile = DB::table('cast_profiles')->where('cast_id', $castId)->first();
        $identity = DB::table('cast_identity_documents')->where('cast_id', $castId)->first();
        $bank = DB::table('bank_accounts')
            ->where('holder_type', 'casts')
            ->where('holder_id', $castId)
            ->first();

        $providers = DB::table('cast_providers')->where('cast_id', $castId)->get();

        // 取引履歴（応募／採用／入金フロー）
        $applications = DB::table('shop_job_applications')
            ->leftJoin('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->leftJoin('shops', 'shop_jobs.shop_id', '=', 'shops.id')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->where('shop_job_applications.cast_id', $castId)
            ->orderByDesc('shop_job_applications.created_at')
            ->get([
                'shop_job_applications.id',
                'shop_job_applications.status',
                'shop_job_applications.result_date',
                'shop_job_applications.real_start_date',
                'shop_job_applications.created_at',
                'shop_job_applications.updated_at',
                'shop_jobs.shop_id',
                'shop_profiles.shop_name',
            ]);

        $depositRows = DB::table('application_deposits')
            ->leftJoin('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->leftJoin('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->leftJoin('shop_profiles', 'shop_jobs.shop_id', '=', 'shop_profiles.shop_id')
            ->where('shop_job_applications.cast_id', $castId)
            ->orderByDesc('application_deposits.created_at')
            ->get([
                'application_deposits.id',
                'application_deposits.status',
                'application_deposits.invoice_number',
                'application_deposits.bonus_amount',
                'application_deposits.cast_transfer_amount',
                'application_deposits.invoice_issued_at',
                'application_deposits.cast_transferred_at',
                'application_deposits.completed_at',
                'shop_profiles.shop_name',
            ]);

        $operationSummary = $this->billingManagementService->getOperationSummaryByEntity('cast')[$castId] ?? null;

        $isUnlocked = $this->privateAccessService->isUnlocked('cast', $castId);
        $unlockTtl = $this->privateAccessService->unlockedSecondsRemaining('cast', $castId);

        return view('admin.casts.show', [
            'castId' => $castId,
            'cast' => $cast,
            'profile' => $profile,
            'identity' => $identity,
            'bank' => $bank,
            'providers' => $providers,
            'applications' => $applications,
            'depositRows' => $depositRows,
            'operationSummary' => $operationSummary,
            'isUnlocked' => $isUnlocked,
            'unlockTtlSeconds' => $unlockTtl,
            'displayName' => $profile->nickname ?? ($profile->name ?? '未設定'),
            'totalEarnings' => $this->sumCastEarnings((string) $castId),
        ]);
    }

    public function unlockPrivate(Request $request, string $castId): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $ok = $this->privateAccessService->unlockWithPassword('cast', $castId, (string) $request->input('password'));
        if (!$ok) {
            $this->opLog->record('cast.private_unlock.fail', 'cast', $castId, 'パスワード認証失敗');
            return redirect()->route('admin.casts.show', $castId)
                ->with('private_unlock_error', '管理者パスワードが一致しません。');
        }
        $this->opLog->record('cast.private_unlock', 'cast', $castId, 'キャスト非公開情報の解除');
        return redirect()->route('admin.casts.show', $castId)
            ->with('status', '非公開情報を解除しました（' . (int) ($this->privateAccessService->ttlSeconds() / 60) . '分間有効）。');
    }

    public function lockPrivate(string $castId): RedirectResponse
    {
        $this->privateAccessService->lock('cast', $castId);
        return redirect()->route('admin.casts.show', $castId)
            ->with('status', '非公開情報を再度ロックしました。');
    }

    /**
     * キャストアカウントを停止（status = 2）
     */
    public function suspend(Request $request, string $castId): RedirectResponse
    {
        $cast = DB::table('casts')->where('id', $castId)->first();
        abort_unless($cast, 404);

        DB::table('casts')->where('id', $castId)->update([
            'status' => 2,
            'updated_at' => now(),
        ]);
        $this->opLog->record('cast.suspend', 'cast', $castId, 'キャスト停止: ' . $castId);

        $redirect = $request->input('redirect_to') === 'show'
            ? redirect()->route('admin.casts.show', $castId)
            : redirect()->route('admin.casts.index');
        return $redirect->with('status', 'キャストアカウントを停止しました。');
    }

    /**
     * キャストアカウントの停止を解除（status = 1）
     */
    public function unsuspend(Request $request, string $castId): RedirectResponse
    {
        $cast = DB::table('casts')->where('id', $castId)->first();
        abort_unless($cast, 404);

        DB::table('casts')->where('id', $castId)->update([
            'status' => 1,
            'updated_at' => now(),
        ]);
        $this->opLog->record('cast.unsuspend', 'cast', $castId, 'キャスト停止解除: ' . $castId);

        $redirect = $request->input('redirect_to') === 'show'
            ? redirect()->route('admin.casts.show', $castId)
            : redirect()->route('admin.casts.index');
        return $redirect->with('status', 'キャストアカウントの停止を解除しました。');
    }

    private function sumCastEarnings(string $castId): int
    {
        return (int) DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->where('shop_job_applications.cast_id', $castId)
            ->whereNotNull('application_deposits.cast_transferred_at')
            ->sum('application_deposits.cast_transfer_amount');
    }
}
