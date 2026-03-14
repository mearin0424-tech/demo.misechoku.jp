<?php

namespace App\Http\Controllers\Shops;

use App\Rules\KouzaMeig;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MypageController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly DocumentReviewService $documentReviewService
    )
    {
    }

    public function index()
    {
        $shopId = $this->currentShopId();

        $row = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('reviews', 'shops.id', '=', 'reviews.shop_id')
            ->where('shops.id', $shopId)
            ->select(
                'shops.id',
                'shops.status',
                'shops.license_status',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.addr2',
                'shop_profiles.addr3',
                'shop_profiles.catch',
                'shop_profiles.overview',
                'shop_profiles.message',
                'shop_profiles.updated_at as profile_updated_at',
                DB::raw('AVG(reviews.eva) as avg_eva'),
                DB::raw('COUNT(reviews.id) as review_count')
            )
            ->groupBy(
                'shops.id',
                'shops.status',
                'shops.license_status',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.addr2',
                'shop_profiles.addr3',
                'shop_profiles.catch',
                'shop_profiles.overview',
                'shop_profiles.message',
                'shop_profiles.updated_at'
            )
            ->first();

        $documentData = $this->documentReviewService->getShopLicensePageData($shopId);

        $shopData = [
            'shop_name'    => $row->shop_name ?? 'ショップ',
            'word'         => $row->catch ?? ($row->message ?? '最高級の空間で、最高の出会いを。'),
            'review_avg'   => $row && $row->avg_eva ? round((float)$row->avg_eva, 1) : 0.0,
            'review_count' => $row ? (int)$row->review_count : 0,
            'pref'         => $row->pref ?? '',
            'city'         => $row->city ?? '',
            'addr1'        => trim(($row->addr2 ?? '') . ' ' . ($row->addr3 ?? '')),
            'overview'     => $row->overview ?? '',
            'appeal_updated_at' => !empty($row?->profile_updated_at)
                ? Carbon::parse($row->profile_updated_at)->format('Y/m/d H:i')
                : null,
            'approval'     => $documentData['all_approved'] ? 1 : 0,
        ];

        $subImages = [];
        $shopImages = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->get();
        foreach ($shopImages as $i => $img) {
            $subImages[] = ['id' => $img->id, 'url' => $this->shopImageUrl($img->image_path)];
        }
        if (empty($subImages)) {
            $subImages = [];
        }

        return view('shops.mypage.index', [
            'pageId'    => 'mypage',
            'shopData'  => $shopData,
            'subImages' => $subImages,
            'documents' => $documentData['documents'],
            'allDocumentsApproved' => $documentData['all_approved'],
        ]);
    }

    public function payment()
    {
        $shopId = $this->currentShopId();
        $paymentData = $this->billingManagementService->getShopPaymentPageData($shopId);
        $currentDeposit = $paymentData['current'] ?? null;

        $jobIds = DB::table('shop_jobs')->where('shop_id', $shopId)->pluck('id');
        $applications = collect();
        if ($jobIds->isNotEmpty()) {
            $applications = DB::table('shop_job_applications')
                ->join('casts', 'shop_job_applications.cast_id', '=', 'casts.id')
                ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                ->whereIn('shop_job_applications.shop_job_id', $jobIds)
                ->select(
                    'shop_job_applications.*',
                    'cast_profiles.nickname',
                    'cast_profiles.birthday'
                )
                ->get();
        }

        $candidates = [];
        foreach ($applications as $app) {
            $statusInfo = $this->mapApplicationStatus((int)$app->status);
            $birthday = $app->birthday ? Carbon::parse($app->birthday) : null;
            $candidates[] = [
                'id'            => $app->id,
                'name'          => $app->nickname ?? $app->cast_id,
                'age'           => $birthday ? $birthday->age : null,
                'job_type'      => '本入店',
                'status_label'  => $statusInfo['label'],
                'status_tag'    => $statusInfo['tag'],
                'next_step'     => $statusInfo['next'],
                'interview_at'  => $app->result_date,
                'deadline_at'   => null,
                'last_message'  => null,
            ];
        }

        $calendarEvents = [];
        foreach ($applications as $app) {
            if ($app->result_date) {
                $calendarEvents[] = [
                    'date'  => $app->result_date,
                    'time'  => null,
                    'type'  => 'interview',
                    'actor' => 'shop',
                    'label' => ($this->mapApplicationStatus((int)$app->status)['label'] ?? 'やり取り中') . '（' . ($app->nickname ?? $app->cast_id) . '）',
                ];
            }
        }
        if ($currentDeposit && !empty($currentDeposit['invoice_issued_at'])) {
            $calendarEvents[] = [
                'date'  => Carbon::parse($currentDeposit['invoice_issued_at'])->toDateString(),
                'time'  => Carbon::parse($currentDeposit['invoice_issued_at'])->format('H:i'),
                'type'  => 'deposit',
                'actor' => 'admin',
                'label' => '運営 → 店舗請求書発行',
            ];
        }

        return view('shops.mypage.payment', [
            'pageId'         => 'manage',
            'invoices'       => $paymentData['invoices'],
            'summary'        => $paymentData['summary'],
            'candidates'     => $candidates,
            'calendarEvents' => $calendarEvents,
            'depositFlow'    => $paymentData['flow'],
            'shopBank'       => $paymentData['bank'],
            'paymentForm'    => $paymentData['payment_form'],
            'currentDeposit' => $currentDeposit,
            'approvalTarget' => $paymentData['approval_target'],
            'canReportPayment' => $paymentData['can_report_payment'],
        ]);
    }

    /**
     * 店舗側の振込先口座情報登録（デモ用）
     */
    public function updateBank(Request $request)
    {
        $request->merge(
            $this->billingManagementService->normalizeBankAccountData($request->all())
        );

        $request->validate([
            'bank_code'      => ['required', 'regex:/^\d{4}$/'],
            'bank_name'      => 'required|string|max:100',
            'branch_code'    => ['required', 'regex:/^\d{3}$/'],
            'branch_name'    => 'required|string|max:100',
            'account_type'   => 'required|in:ordinary,current',
            'account_number' => ['required', 'regex:/^\d{7,8}$/'],
            'account_name'   => ['required', 'string', 'max:100', new KouzaMeig()],
        ], [
            'bank_code.required' => '金融機関を候補から選択してください。',
            'bank_code.regex' => '金融機関コードが不正です。',
            'branch_code.required' => '支店を候補から選択してください。',
            'branch_code.regex' => '支店コードが不正です。',
            'account_number.required' => '口座番号を入力してください。',
            'account_number.regex' => '口座番号は7桁または8桁の数字で入力してください。',
            'account_name.required' => '口座名義（カナ）を入力してください。',
        ]);

        $this->billingManagementService->saveShopBankAccount($this->currentShopId(), $request->only([
            'bank_code',
            'bank_name',
            'bank_name_kana',
            'branch_code',
            'branch_name',
            'branch_name_kana',
            'account_type',
            'account_number',
            'account_name',
        ]));

        return response()->json([
            'success' => true,
            'message' => '店舗口座情報を保存しました。',
        ]);
    }

    /**
     * 営業許可証・風営許可証のアップロード
     * ※ 現段階ではモックとしてストレージに保存し、審査・承認は別途運営画面で行う想定
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:business,entertainment',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:8192',
            'expired_at' => 'nullable|date',
        ]);

        if ($request->hasFile('file')) {
            $type = (string) $request->input('type');
            $document = $this->documentReviewService->uploadShopLicenseDocument(
                $this->currentShopId(),
                $type,
                $request->file('file'),
                $request->input('expired_at')
            );

            return response()->json([
                'success' => true,
                'message' => '書類をアップロードしました。運営による確認・承認をお待ちください。',
                'type'    => $type,
                'path'    => Storage::url($document->image_path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ファイルが選択されていません。',
        ], 400);
    }

    /**
     * 店舗側：ノルマ達成・店舗審査完了
     */
    public function approveDeposit(Request $request)
    {
        $result = $this->billingManagementService->confirmDepositForShop(
            $this->currentShopId(),
            $request->all()
        );

        return redirect()
            ->route('shop.mypage.payment.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * 店舗側：運営へ入金完了
     */
    public function payToPlatform(Request $request)
    {
        $payload = $request->validate([
            'reported_amount' => 'required|integer|min:1',
            'reported_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
        ]);

        $result = $this->billingManagementService->reportShopPayment($this->currentShopId(), $payload);

        return redirect()
            ->route('shop.mypage.payment.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * ログイン中店舗に紐づく直近の application_deposits.id を取得
     */
    private function getLatestDepositIdForShop(): ?int
    {
        $row = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->where('shop_jobs.shop_id', $this->currentShopId())
            ->orderByDesc('application_deposits.id')
            ->value('application_deposits.id');

        return $row ? (int)$row : null;
    }

    /**
     * shop_job_applications.status をラベル・タグ・次アクションに変換
     * 1:やり取り中, 2:面談日調整中, 3:面談日決定, 4:採用, 5:不採用
     */
    private function mapApplicationStatus(int $status): array
    {
        return match ($status) {
            1 => ['label' => 'やり取り中',       'tag' => 'in_progress',     'next' => 'メッセージで日程調整'],
            2 => ['label' => '面談日調整中',     'tag' => 'interview_pending', 'next' => '候補日の返信待ち'],
            3 => ['label' => '面談日決定',       'tag' => 'interview_fixed',  'next' => '当日の来店フォロー'],
            4 => ['label' => '採用',             'tag' => 'hired',           'next' => '入店手続き'],
            5 => ['label' => '不採用',           'tag' => 'rejected',        'next' => '—'],
            default => ['label' => 'やり取り中', 'tag' => 'in_progress',     'next' => '—'],
        };
    }

    /**
     * application_deposits.status から3者分のステータスを構成（キャスト側と同一マッピング）
     */
    private function buildDepositFlowStateFromDb($deposit): array
    {
        if (!$deposit) {
            return ['cast' => '未申請', 'shop' => '未稼働', 'admin' => '未稼働'];
        }

        $status = (int)$deposit->status;

        return match ($status) {
            1 => ['cast' => '申請中', 'shop' => '承認待ち', 'admin' => '提出待ち'],
            2 => ['cast' => '申請中', 'shop' => '店舗確認中', 'admin' => '請求待ち'],
            3 => ['cast' => 'お振込準備中', 'shop' => 'お支払い待ち', 'admin' => '店舗へ請求中'],
            4 => ['cast' => 'お振込準備中', 'shop' => '入金報告済', 'admin' => '店舗入金確認中'],
            5 => ['cast' => 'お振込準備中', 'shop' => 'お支払い完了', 'admin' => '店舗入金確認済'],
            6 => ['cast' => 'お振込手続き中', 'shop' => 'お支払い完了', 'admin' => 'キャスト振込済'],
            7 => ['cast' => '完了', 'shop' => '完了', 'admin' => '完了'],
            default => ['cast' => '未申請', 'shop' => '未稼働', 'admin' => '未稼働'],
        };
    }

    /** 店舗画像パスを表示用URLに変換（uploads/ または storage 対応） */
    private function shopImageUrl(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }
        return asset(ltrim($path, '/'));
    }

    private function currentShopId(): string
    {
        return (string) auth()->guard('shop')->user()->shop_id;
    }
}