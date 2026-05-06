<?php

namespace App\Http\Controllers\Shops;

use App\Models\ShopLicenseDocument;
use App\Rules\KouzaMeig;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;
use App\Http\Controllers\Controller;
use App\Support\ShopBusinessHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $rowQ = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('reviews', 'shops.id', '=', 'reviews.shop_id')
            ->where('shops.id', $shopId)
            ->select(
                'shops.id',
                'shops.status',
                'shops.license_status',
                'shop_profiles.shop_name',
                'shop_profiles.zip',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.industry_id',
                'shop_profiles.updated_at as profile_updated_at',
                DB::raw('AVG(reviews.eva) as avg_eva'),
                DB::raw('COUNT(reviews.id) as review_count')
            )
            ->groupBy(
                'shops.id',
                'shops.status',
                'shops.license_status',
                'shop_profiles.shop_name',
                'shop_profiles.zip',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.industry_id',
                'shop_profiles.updated_at'
            );
        if (Schema::hasColumn('shop_profiles', 'addr')) {
            $rowQ->addSelect('shop_profiles.addr as addr2');
            if (Schema::hasColumn('shop_profiles', 'building')) {
                $rowQ->addSelect('shop_profiles.building as addr3');
            } else {
                $rowQ->addSelect(DB::raw("'' as addr3"));
            }
            $rowQ->groupBy('shop_profiles.addr');
            if (Schema::hasColumn('shop_profiles', 'building')) {
                $rowQ->groupBy('shop_profiles.building');
            }
        } else {
            if (Schema::hasColumn('shop_profiles', 'addr2')) {
                $rowQ->addSelect('shop_profiles.addr2');
                $rowQ->groupBy('shop_profiles.addr2');
            } else {
                $rowQ->addSelect(DB::raw("'' as addr2"));
            }
            if (Schema::hasColumn('shop_profiles', 'addr3')) {
                $rowQ->addSelect('shop_profiles.addr3');
                $rowQ->groupBy('shop_profiles.addr3');
            } else {
                $rowQ->addSelect(DB::raw("'' as addr3"));
            }
        }
        if (Schema::hasColumn('shop_profiles', 'station1')) {
            $rowQ->addSelect('shop_profiles.station1');
            $rowQ->groupBy('shop_profiles.station1');
        } else {
            $rowQ->addSelect(DB::raw("'' as station1"));
        }
        $row = $rowQ->first();

        $shopPost = DB::table('shop_posts')
            ->where('shop_id', $shopId)
            ->when(
                Schema::hasColumn('shop_posts', 'type'),
                fn ($q) => $q->where('type', 2)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
        $hitokotoBody = $shopPost && isset($shopPost->body) ? (string) $shopPost->body : '';
        $hitokotoUpdated = $shopPost && !empty($shopPost->updated_at)
            ? $shopPost->updated_at
            : ($shopPost && !empty($shopPost->created_at) ? $shopPost->created_at : null);

        $documentData = $this->documentReviewService->getShopLicensePageData($shopId);
        $badges = $this->billingManagementService->getShopBadges($shopId);

        $jobIds = DB::table('shop_jobs')->where('shop_id', $shopId)->pluck('id');
        $applicantCount = 0;
        $hiredCount = 0;
        $recruitStatus = '未設定';
        $paymentPendingCount = 0;
        if ($jobIds->isNotEmpty()) {
            $applicantCount = DB::table('shop_job_applications')
                ->whereIn('shop_job_id', $jobIds)
                ->select('cast_id')
                ->groupBy('cast_id')
                ->get()
                ->count();
            $hiredCount = (int) DB::table('shop_job_applications')
                ->whereIn('shop_job_id', $jobIds)
                ->where('status', 4)
                ->count();

            $paymentPendingCount = (int) DB::table('application_deposits')
                ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
                ->whereIn('shop_job_applications.shop_job_id', $jobIds)
                ->where('application_deposits.status', 3)
                ->count();
        }

        $jobStatusQ = DB::table('shop_jobs')->where('shop_id', $shopId);
        if (Schema::hasColumn('shop_jobs', 'job_type') && !Schema::hasColumn('shop_jobs', 'regular_status')) {
            $jobStatusQ->where('job_type', 1);
        }
        if (Schema::hasColumn('shop_jobs', 'regular_status')) {
            $jobStatus = $jobStatusQ->value('regular_status');
        } else {
            $jobStatus = $jobStatusQ->value('status');
        }
        if ($jobStatus !== null) {
            $recruitStatus = ((int) $jobStatus) === 1 ? '掲載中' : '掲載停止中';
        }

        $shopData = [
            'shop_name'    => $row->shop_name ?? 'ショップ',
            'word'         => $hitokotoBody !== '' ? $hitokotoBody : '最高級の空間で、最高の出会いを。',
            'review_avg'   => $row && $row->avg_eva ? round((float)$row->avg_eva, 1) : 0.0,
            'review_count' => $row ? (int)$row->review_count : 0,
            'applicant_count' => $applicantCount,
            'hired_count'  => $hiredCount,
            'pref'         => $row->pref ?? '',
            'city'         => $row->city ?? '',
            'addr1'        => trim(($row->addr2 ?? '') . ' ' . ($row->addr3 ?? '')),
            'overview'     => '',
            'appeal_updated_at' => $hitokotoUpdated
                ? Carbon::parse($hitokotoUpdated)->format('Y/m/d H:i')
                : (!empty($row?->profile_updated_at)
                    ? Carbon::parse($row->profile_updated_at)->format('Y/m/d H:i')
                    : null),
            'approval'     => $documentData['all_approved'] ? 1 : 0,
            'badges'       => [
                'good_payer' => !empty($badges['good_payer']),
            ],
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

        $jobRowQ = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->select('working_hours', 'working_day', 'regular_holiday');
        if (Schema::hasColumn('shop_jobs', 'job_type') && !Schema::hasColumn('shop_jobs', 'regular_status')) {
            $jobRowQ->where('job_type', 1);
        }
        $jobRow = $jobRowQ->first();
        $industryName = null;
        if (!empty($row?->industry_id)) {
            $industryName = DB::table('industries')
                ->where('id', $row->industry_id)
                ->value('name');
        }
        $shopTagGroups = $this->resolveShopInfoTagGroups($shopId);

        $profileRow = DB::table('shop_profiles')->where('shop_id', $shopId)->first();
        $businessHoursShop = '';
        if ($profileRow && Schema::hasColumn('shop_profiles', 'open_time')) {
            $businessHoursShop = ShopBusinessHours::formatDisplay(
                $profileRow->open_time ?? null,
                (int) ($profileRow->close_is_last ?? 0),
                $profileRow->close_time ?? null
            );
        }
        $stationLines = [];
        if (Schema::hasTable('shop_stations')) {
            $stationLines = DB::table('shop_stations')
                ->where('shop_id', $shopId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('station_name')
                ->map(fn ($n) => trim((string) $n))
                ->filter()
                ->values()
                ->all();
        }
        $addrStreetOnly = '';
        if ($profileRow && Schema::hasColumn('shop_profiles', 'addr')) {
            $addrStreetOnly = trim(implode(' ', array_filter([
                $profileRow->addr ?? '',
                $profileRow->building ?? '',
            ])));
        }
        if ($addrStreetOnly === '' && $row) {
            $addrStreetOnly = trim(($row->addr2 ?? '') . ' ' . ($row->addr3 ?? ''));
        }

        return view('shops.mypage.index', [
            'pageId'    => 'mypage',
            'shopData'  => $shopData,
            'subImages' => $subImages,
            'documents' => $documentData['documents'],
            'allDocumentsApproved' => $documentData['all_approved'],
            'shopInfo' => [
                'shop_name' => $row->shop_name ?? '',
                'word' => $hitokotoBody,
                'industry' => $industryName,
                'zip' => $row->zip ?? '',
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'addr1' => $addrStreetOnly,
                'nearest_station' => $stationLines[0] ?? ($row->station1 ?? ''),
                'nearest_stations' => $stationLines,
                'business_hours_shop' => $businessHoursShop,
                'tel' => ($profileRow && Schema::hasColumn('shop_profiles', 'tel')) ? (string) ($profileRow->tel ?? '') : '',
                'working_hours' => $jobRow?->working_hours ?? '',
                'working_days' => $jobRow?->working_day ?? '',
                'regular_holiday' => $jobRow?->regular_holiday ?? '',
                'concept' => '',
                'tag_groups' => $shopTagGroups,
            ],
            'menuData' => [
                'recruit_status' => $recruitStatus,
                'hired_count' => $hiredCount,
                'payment_pending_count' => $paymentPendingCount,
            ],
        ]);
    }

    /**
     * 新規登録直後など：許可証2種の提出に誘導する画面
     */
    public function documentsOnboarding()
    {
        return redirect()
            ->route('shop.mypage.index')
            ->with('message', '許可証の提出はマイページから操作してください。');
    }

    public function manageLicenseDocument(string $type)
    {
        if (!in_array($type, ['business', 'entertainment'], true)) {
            abort(404);
        }

        $shopId = $this->currentShopId();
        $documentData = $this->documentReviewService->getShopLicensePageData($shopId);
        $document = collect($documentData['documents'])->firstWhere('key', $type);

        if (!$document) {
            abort(404);
        }

        return view('shops.mypage.documents-manage', [
            'pageId' => 'documents_manage',
            'document' => $document,
        ]);
    }

    /**
     * ひとこと（キャッチコピー）をモーダルから更新
     */
    public function updateWord(Request $request)
    {
        $request->validate([
            'word' => 'nullable|string|max:500',
        ]);

        $shopId = $this->currentShopId();
        $word = $request->input('word', '');
        $word = is_string($word) ? trim($word) : '';

        $now = Carbon::now();
        $matchKey = ['shop_id' => $shopId];
        if (Schema::hasColumn('shop_posts', 'type')) {
            $matchKey['type'] = 2;
        }
        DB::table('shop_posts')->updateOrInsert(
            $matchKey,
            [
                'body'       => $word,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return response()->json([
            'success' => true,
            'appeal_updated_at' => $now->format('Y/m/d H:i'),
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
     * 自店の許可証ファイルを閲覧（ログイン店舗のみ・storage 直 URL に依存しない）
     */
    public function viewLicenseDocument(string $type)
    {
        if (!in_array($type, ['business', 'entertainment'], true)) {
            abort(404);
        }

        $shopId = $this->currentShopId();
        $document = ShopLicenseDocument::query()
            ->where('shop_id', $shopId)
            ->where('type', $type)
            ->first();

        if (!$document || empty($document->image_path)) {
            abort(404, '書類が見つかりません。');
        }

        $relative = $this->documentReviewService->shopLicenseRelativePublicPath($document->image_path);
        if ($relative === null || !Storage::disk('public')->exists($relative)) {
            abort(404, 'ファイルが見つかりません。');
        }

        $absolute = Storage::disk('public')->path($relative);
        $mime = @mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename*=UTF-8\'\'' . rawurlencode(basename($relative)),
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
            'expired_at' => 'nullable|date|after_or_equal:today',
        ], [
            'expired_at.date' => '有効期限は日付形式で入力してください。',
            'expired_at.after_or_equal' => '営業許可証の有効期限には本日以降の日付を入力してください。',
        ]);

        if ($request->hasFile('file')) {
            $type = (string) $request->input('type');
            $current = ShopLicenseDocument::query()
                ->where('shop_id', $this->currentShopId())
                ->where('type', $type)
                ->first();
            if ($current && in_array((int) $current->status, [ShopLicenseDocument::STATUS_PENDING, ShopLicenseDocument::STATUS_APPROVED], true)) {
                if (!$request->expectsJson()) {
                    return redirect()
                        ->route('shop.mypage.documents.manage', ['type' => $type])
                        ->withErrors(['file' => '提出済みのため差し替えできません。先に「提出取り下げ」を行ってください。']);
                }

                return response()->json([
                    'success' => false,
                    'message' => '提出済みのため差し替えできません。先に「提出取り下げ」を行ってください。',
                ], 422);
            }

            $document = $this->documentReviewService->uploadShopLicenseDocument(
                $this->currentShopId(),
                $type,
                $request->file('file'),
                $request->input('expired_at')
            );

            if (!$request->expectsJson()) {
                return redirect()
                    ->route('shop.mypage.documents.manage', ['type' => $type])
                    ->with('message', '書類をアップロードしました。内容を確認して「提出」を押してください。');
            }

            return response()->json([
                'success' => true,
                'message' => '書類をアップロードしました。内容を確認して「提出」を押してください。',
                'type' => $type,
                'view_url' => route('shop.mypage.documents.show', ['type' => $type]),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ファイルが選択されていません。',
        ], 400);
    }

    public function requestDocumentReview(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:business,entertainment',
            'expired_at' => 'nullable|date|required_if:type,business|after_or_equal:today',
        ], [
            'expired_at.required_if' => '営業許可証の有効期限を入力してください。',
            'expired_at.date' => '有効期限は日付形式で入力してください。',
            'expired_at.after_or_equal' => '営業許可証の有効期限には本日以降の日付を入力してください。',
        ]);

        try {
            $this->documentReviewService->requestShopDocumentReview(
                $this->currentShopId(),
                (string) $data['type'],
                isset($data['expired_at']) ? (string) $data['expired_at'] : null
            );
        } catch (\RuntimeException $e) {
            if (!$request->expectsJson()) {
                return redirect()
                    ->route('shop.mypage.documents.manage', ['type' => (string) $data['type']])
                    ->withErrors(['document' => $e->getMessage()]);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if (!$request->expectsJson()) {
            return redirect()
                ->route('shop.mypage.documents.manage', ['type' => (string) $data['type']])
                ->with('message', '提出が完了しました。運営の審査をお待ちください。');
        }

        return response()->json([
            'success' => true,
            'message' => '提出が完了しました。運営の審査をお待ちください。',
        ]);
    }

    public function withdrawDocumentReview(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:business,entertainment',
        ]);

        try {
            $this->documentReviewService->withdrawShopDocumentReview($this->currentShopId(), (string) $data['type']);
        } catch (\RuntimeException $e) {
            if (!$request->expectsJson()) {
                return redirect()
                    ->route('shop.mypage.documents.manage', ['type' => (string) $data['type']])
                    ->withErrors(['document' => $e->getMessage()]);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if (!$request->expectsJson()) {
            return redirect()
                ->route('shop.mypage.documents.manage', ['type' => (string) $data['type']])
                ->with('message', '提出を取り下げました。ファイルを再アップロードしてから審査依頼してください。');
        }

        return response()->json([
            'success' => true,
            'message' => '提出を取り下げました。ファイルを再アップロードしてから審査依頼してください。',
        ]);
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
            ->route('shop.mypage.management', ['tab' => 'payment'])
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
            ->route('shop.mypage.management', ['tab' => 'payment'])
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

    /**
     * 店舗プロフィールに紐づく shop_tag_relations を、新スキーマ (shop_tags target=shop) で
     * カテゴリごとにグループ化して返す。
     *
     * @return array<int, array{label: string, tags: array<int, string>}>
     */
    private function resolveShopInfoTagGroups(string $shopId): array
    {
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('shop_tag_relations') || !$schema->hasTable('shop_tags')) {
            return [];
        }

        $definitions = [
            ['label' => '店内の雰囲気・客層', 'category' => 'atmosphere'],
            ['label' => '設備・アクセス',     'category' => 'facility'],
        ];

        $groups = [];
        foreach ($definitions as $def) {
            $names = $this->fetchShopTagNames($shopId, $def['category']);
            if (!empty($names)) {
                $groups[] = ['label' => $def['label'], 'tags' => $names];
            }
        }

        return $groups;
    }

    /**
     * shop_tag_relations -> shop_tags(target='shop') から名前のみ取得する。
     */
    private function fetchShopTagNames(string $shopId, string $category): array
    {
        return DB::table('shop_tag_relations as r')
            ->join('shop_tags as t', 'r.tag_id', '=', 't.id')
            ->where('r.shop_id', $shopId)
            ->where('r.tag_type', $category)
            ->where('t.target', 'shop')
            ->where('t.category', $category)
            ->where('t.del_flg', 0)
            ->orderBy('t.sort_order')
            ->orderBy('t.id')
            ->pluck('t.name')
            ->filter()
            ->values()
            ->all();
    }
}