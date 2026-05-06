<?php

namespace App\Http\Controllers\Casts;

use App\Rules\KouzaMeig;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;
use App\Support\ShopJobApplicationView;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly DocumentReviewService $documentReviewService
    )
    {
    }

    /**
     * キャスト用マイページ（プロフィール確認＝shop/castprofileview と同じ内容）
     */
    public function index()
    {
        $cast = $this->getCastFromDatabase($this->currentCastId());
        $castForProfile = $cast;
        $castForProfile['reviews'] = [];
        // ギャラリー用：id + url（削除APIで id を使用）
        $subImages = [];
        foreach (array_values($cast['images']) as $i => $img) {
            $subImages[] = [
                'id'  => is_array($img) ? ($img['id'] ?? $i + 1) : $i + 1,
                'url' => is_array($img) ? ($img['url'] ?? '') : $img,
            ];
        }
        return view('casts.mypage.index', [
            'pageId'       => 'mypage',
            'cast'         => $castForProfile,
            'isOwn'        => true,
            'subImages'    => $subImages,
        ]);
    }

    /**
     * 採用・入金管理（採用状況と請求・入金を1画面に統合）
     */
    public function employment()
    {
        $castId = $this->currentCastId();

        $employmentQuery = DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shops', 'shop_jobs.shop_id', '=', 'shops.id')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->where('shop_job_applications.cast_id', $castId)
            ->orderByDesc('shop_job_applications.updated_at')
            ->select(
                'shop_job_applications.id as application_id',
                'shop_job_applications.status',
                'shop_job_applications.result_date',
                'shops.id as shop_id',
                'shop_profiles.shop_name'
            );

        foreach ([
            'hired_regular_hourly_wage',
            'applied_bonus_reward',
            'applied_bonus_remarks',
            'applied_bonus_condition',
            'applied_norma_day',
            'applied_norma_hours',
            'applied_regular_hourly_wage',
        ] as $col) {
            if (Schema::hasColumn('shop_job_applications', $col)) {
                $employmentQuery->addSelect('shop_job_applications.' . $col);
            }
        }

        $employments = $employmentQuery
            ->get()
            ->map(function ($row) {
                $status = $this->mapApplicationStatus((int) $row->status);
                $bonusLines = [];
                $norma = ShopJobApplicationView::bonusNormaSummaryAtApplication($row);
                if ($norma !== '') {
                    $bonusLines[] = 'ボーナス条件（応募時）: ' . $norma;
                }
                $br = ShopJobApplicationView::bonusRewardAtApplication($row);
                if ($br !== null && $br > 0) {
                    $bonusLines[] = 'ボーナス金額（応募時）: ¥' . number_format($br);
                }
                $remarks = property_exists($row, 'applied_bonus_remarks') && $row->applied_bonus_remarks !== null
                    ? trim((string) $row->applied_bonus_remarks)
                    : '';
                if ($remarks !== '') {
                    $bonusLines[] = '補足: ' . $remarks;
                }
                $cond = ShopJobApplicationView::bonusConditionAtApplication($row);
                if ($cond !== '') {
                    $bonusLines[] = '達成条件: ' . $cond;
                }
                $hiredWage = ShopJobApplicationView::wageAtHire($row);

                return [
                    'application_id' => (int) $row->application_id,
                    'status_code' => (int) $row->status,
                    'shop_name' => $row->shop_name ?: $row->shop_id,
                    'status_label' => $status['label'],
                    'status_class' => $status['class'],
                    'applied_at' => !empty($row->result_date)
                        ? Carbon::parse($row->result_date)->format('Y-m-d')
                        : null,
                    'link' => route('cast.talk.room', $row->shop_id),
                    'bonus_at_apply_lines' => $bonusLines,
                    'hired_hourly_wage_display' => $hiredWage,
                ];
            })
            ->all();

        $paymentData = $this->billingManagementService->getCastPaymentPageData($castId);

        return view('casts.mypage.employment', [
            'pageId' => 'mypage',
            'employments' => $employments,
            'payments' => $paymentData['payments'],
            'depositFlow' => $paymentData['flow'],
            'castBank' => $paymentData['bank'],
            'currentDeposit' => $paymentData['current'],
            'canRequestDeposit' => $paymentData['can_request'],
            'requestDisabledReason' => $paymentData['request_disabled_reason'],
            'requestTarget' => $paymentData['request_target'],
        ]);
    }

    /**
     * 本人確認画面
     */
    public function identity()
    {
        $castId = $this->currentCastId();
        $identityData = $this->documentReviewService->getCastIdentityPageData($castId);

        return view('casts.mypage.identity', [
            'pageId' => 'mypage',
            'identityStatus' => $identityData['status'],
            'identityDocuments' => $identityData['documents'],
            'latestIdentityDocument' => $identityData['latest_document'],
        ]);
    }

    /**
     * 本人確認書類アップロード（デモ用）
     */
    public function uploadIdentity(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:driver_license,passport,my_number',
            'front_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:8192',
            'back_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:8192',
            'expired_at' => 'nullable|date',
        ]);

        $castId = $this->currentCastId();
        $this->documentReviewService->uploadCastIdentityDocument(
            $castId,
            (string) $request->input('type'),
            $request->file('front_file'),
            $request->file('back_file'),
            $request->input('expired_at')
        );

        return response()->json([
            'success' => true,
            'message' => '本人確認書類をアップロードしました。運営による確認・承認をお待ちください。',
        ]);
    }

    /**
     * キャスト画像アップロード（DB: cast_images type=1 に保存）
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        try {
            $dir = public_path('uploads/casts/gallery');
            File::ensureDirectoryExists($dir);
            $file = $request->file('image');
            $name = $file->hashName();
            $file->move($dir, $name);
            $path = 'uploads/casts/gallery/' . $name;
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => '画像の保存に失敗しました。'], 500);
        }

        $slotIndex = (int) $request->input('slot_index', -1);

        $castId = $this->currentCastId();

        $maxOrder = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->max('main_order');
        $mainOrder = $maxOrder !== null ? $maxOrder + 1 : 0;
        $isMain = $slotIndex === 0 ? 1 : 0;

        $id = DB::table('cast_images')->insertGetId([
            'cast_id'       => $castId,
            'image_path'    => $path,
            'type'          => 1,
            'front_and_back'=> 0,
            'status'        => 0,
            'is_main'       => $isMain,
            'main_order'    => $mainOrder,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $orderedIds = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($imageId) => (int) $imageId)
            ->all();

        $orderedIds = array_values(array_filter($orderedIds, fn ($imageId) => $imageId !== (int) $id));
        $slotIndex = max(0, min($slotIndex, count($orderedIds)));
        array_splice($orderedIds, $slotIndex, 0, [(int) $id]);

        try {
            $this->syncCastImageOrder($castId, $orderedIds);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => '画像の登録に失敗しました。'], 500);
        }

        return response()->json(['success' => true, 'path' => asset($path), 'id' => $id]);
    }

    /**
     * キャスト画像削除（DB: cast_images から削除）
     */
    public function deleteImage(Request $request, $id)
    {
        $castId = $this->currentCastId();

        $currentCount = (int) DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->count();

        $row = DB::table('cast_images')
            ->where('id', $id)
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => '画像が見つかりません'], 404);
        }

        if ($currentCount <= 1) {
            return response()->json(['success' => false, 'message' => 'プロフィール画像は1枚以上必要です。最低1枚は残してください。'], 422);
        }

        $fullPath = str_starts_with($row->image_path ?? '', 'uploads/')
            ? public_path($row->image_path)
            : storage_path('app/' . $row->image_path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
        DB::table('cast_images')->where('id', $id)->delete();

        $orderedIds = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($imageId) => (int) $imageId)
            ->all();

        $this->syncCastImageOrder($castId, $orderedIds);

        return response()->json(['success' => true, 'message' => '画像を削除しました']);
    }

    public function updateImageOrder(Request $request)
    {
        $imageOrder = $request->input('images');

        if (!is_array($imageOrder)) {
            return response()->json(['success' => false, 'message' => 'データが不正です'], 400);
        }

        $orderedIds = array_values(array_unique(array_map('intval', $imageOrder)));
        $this->syncCastImageOrder($this->currentCastId(), $orderedIds);

        return response()->json(['success' => true, 'message' => '並び順を保存しました']);
    }

    /**
     * キャスト側：入金申請（ボーナス条件達成後に押す想定）
     * application_id があればその案件で申請（モーダル「完了」用）
     */
    public function requestDeposit(\Illuminate\Http\Request $request)
    {
        $applicationId = $request->filled('application_id') ? (int) $request->input('application_id') : null;
        $result = $this->billingManagementService->requestDepositForCast(
            $this->currentCastId(),
            $request->all(),
            $applicationId
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        $redirect = redirect()
            ->route('cast.mypage.management')
            ->with($result['success'] ? 'status' : 'error', $result['message']);

        return $result['success'] ? $redirect : $redirect->withInput();
    }

    /**
     * レビューのみ投稿（モーダル用）。成功時はボーナス条件達成確認用の request_target を返す
     */
    public function postReview(Request $request)
    {
        $request->validate(['application_id' => 'required|integer']);
        $applicationId = (int) $request->input('application_id');
        $result = $this->billingManagementService->submitReviewOnly(
            $this->currentCastId(),
            $applicationId,
            $request->all()
        );
        return response()->json($result);
    }

    /**
     * 指定案件のボーナス条件達成確認用データ（採用時点の焼き付け）を返す
     */
    public function getDepositRequestTarget(Request $request)
    {
        $applicationId = $request->input('application_id');
        $shopId = $request->input('shop_id');
        $castId = $this->currentCastId();

        if ($applicationId !== null && $applicationId !== '') {
            $target = $this->billingManagementService->getRequestTargetByApplicationId($castId, (int) $applicationId);
        } elseif ($shopId !== null && $shopId !== '') {
            $target = $this->billingManagementService->getRequestTargetByCastAndShopId($castId, (string) $shopId);
        } else {
            $target = null;
        }

        if ($target === null) {
            return response()->json(['success' => false, 'message' => '対象の採用案件が見つかりません。'], 404);
        }
        return response()->json(['success' => true, 'request_target' => $target]);
    }

    /**
     * キャスト側：入金完了確認（最終確認）
     */
    public function confirmDeposit(\Illuminate\Http\Request $request)
    {
        $result = $this->billingManagementService->confirmCastReceipt($this->currentCastId());

        return redirect()
            ->route('cast.mypage.management')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * 入金フローの現在ステータス（3者分）を組み立てる
     */
    private function buildDepositFlowState(int $step): array
    {
        $map = [
            0 => ['cast' => '未申請',       'shop' => '未稼働',           'admin' => '未稼働'],
            1 => ['cast' => '申請中',       'shop' => '未稼働',           'admin' => '未稼働'],
            2 => ['cast' => '店舗審査中',   'shop' => '店舗審査中',       'admin' => '店舗審査待ち'],
            3 => ['cast' => 'お振込準備中', 'shop' => 'お支払い準備中',   'admin' => '店舗入金依頼中'],
            4 => ['cast' => 'お振込準備中', 'shop' => 'お支払い済み',     'admin' => '店舗入金確認中'],
            5 => ['cast' => 'お振込手続き中', 'shop' => 'お支払い完了', 'admin' => 'キャスト振込済'],
            6 => ['cast' => '完了',         'shop' => '完了',             'admin' => '完了'],
        ];

        return $map[$step] ?? $map[0];
    }

    /**
     * application_deposits.status から3者分のステータスを構成
     */
    private function buildDepositFlowStateFromDb($deposit): array
    {
        if (!$deposit) {
            return $this->buildDepositFlowState(0);
        }

        $status = (int)$deposit->status;

        // 4.Transaction の表をベースに簡易マッピング
        return match ($status) {
            1 => ['cast' => '申請中', 'shop' => '承認待ち', 'admin' => '提出待ち'],
            2 => ['cast' => '申請中', 'shop' => '店舗確認中', 'admin' => '請求待ち'],
            3 => ['cast' => 'お振込準備中', 'shop' => 'お支払い待ち', 'admin' => '店舗へ請求中'],
            4 => ['cast' => 'お振込準備中', 'shop' => '入金報告済', 'admin' => '店舗入金確認中'],
            5 => ['cast' => 'お振込準備中', 'shop' => 'お支払い完了', 'admin' => '店舗入金確認済'],
            6 => ['cast' => 'お振込手続き中', 'shop' => 'お支払い完了', 'admin' => 'キャスト振込済'],
            7 => ['cast' => '完了', 'shop' => '完了', 'admin' => '完了'],
            default => $this->buildDepositFlowState(0),
        };
    }

    /**
     * キャスト側の振込先口座情報登録（デモ用）
     */
    public function updateBank(\Illuminate\Http\Request $request)
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

        $this->billingManagementService->saveCastBankAccount($this->currentCastId(), $request->only([
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
            'message' => 'キャスト口座情報を保存しました。',
        ]);
    }

    /**
     * レビュー一覧（お店の mypage/reviews と同様）
     */
    public function reviews()
    {
        $cast = $this->getCastFromDatabase($this->currentCastId());
        $reviewCount = count($cast['reviews']);
        $castData = [
            'review_avg'   => $reviewCount > 0
                ? round(array_sum(array_column($cast['reviews'], 'score')) / $reviewCount, 1)
                : 0,
            'review_count' => $reviewCount,
        ];
        return view('casts.mypage.reviews', [
            'pageId'    => 'mypage',
            'castData'  => $castData,
            'reviews'   => $cast['reviews'],
        ]);
    }

    /**
     * ひとことを cast_posts に保存（店舗の updateWord と同様）
     */
    public function updateWord(Request $request)
    {
        $request->validate([
            'word' => 'nullable|string|max:500',
        ]);

        $castId = $this->currentCastId();
        $word = $request->input('word', '');
        $word = is_string($word) ? trim($word) : '';

        if (!Schema::hasTable('cast_posts')) {
            return response()->json(['success' => false, 'message' => 'cast_posts テーブルが存在しません。'], 500);
        }

        if (!Schema::hasColumn('cast_posts', 'body')) {
            return response()->json(['success' => false, 'message' => 'cast_posts.body がありません。php artisan migrate を実行してください。'], 500);
        }

        $now = Carbon::now();
        $exists = DB::table('cast_posts')->where('cast_id', $castId)->exists();
        if ($exists) {
            DB::table('cast_posts')->where('cast_id', $castId)->update([
                'body' => $word,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('cast_posts')->insert([
                'cast_id' => $castId,
                'body' => $word,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return response()->json([
            'success' => true,
            'appeal_updated_at' => $now->format('Y/m/d H:i'),
        ]);
    }

    /**
     * キャスト・プロフィール・レビューをDBから取得し、画面用配列に整形
     */
    private function getCastFromDatabase(string $castId): array
    {
        $castRow = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('casts.id', $castId)
            ->select(
                'casts.id',
                'casts.email',
                'casts.status',
                'casts.identity_status',
                'casts.last_login_at',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.birthday',
                'cast_profiles.zip',
                'cast_profiles.pref',
                'cast_profiles.city',
                Schema::hasColumn('cast_profiles', 'addr')
                    ? 'cast_profiles.addr'
                    : DB::raw('NULL as addr'),
                Schema::hasColumn('cast_profiles', 'building')
                    ? 'cast_profiles.building'
                    : DB::raw('NULL as building'),
                'cast_profiles.height',
                'cast_profiles.weight',
                'cast_profiles.bust',
                'cast_profiles.waist',
                'cast_profiles.hip',
                'cast_profiles.work_time',
                'cast_profiles.work_where',
                'cast_profiles.profession',
                'cast_profiles.exp',
                'cast_profiles.pr',
                Schema::hasColumn('cast_profiles', 'personality_type')
                    ? 'cast_profiles.personality_type'
                    : DB::raw('NULL as personality_type'),
                'cast_profiles.updated_at as profile_updated_at'
            )
            ->first();

        if (!$castRow) {
            // データ不在時は空のモック相当を返す
            return $this->buildEmptyCast();
        }

        $birthday = $castRow->birthday ? Carbon::parse($castRow->birthday) : null;
        $age = $birthday ? $birthday->age : null;
        $shiftHope = (string) ($castRow->work_where ?? '');
        $workTime = $this->workTimeKeyFromShift($castRow->work_time);
        $nightWorkExp = ((int) ($castRow->exp ?? 0) === 1 ? 'yes' : 'none');
        $looksTags = $this->getCastTagNamesByType($castId, 'looks');
        $personalityTags = $this->getCastTagNamesByType($castId, 'personality');
        $desiredJob = $this->resolveDesiredJobByIndustries($castId, $castRow->industry_id ?? null);
        $looksSummary = $looksTags !== [] ? implode(' / ', $looksTags) : '';
        $personalitySummary = $personalityTags !== [] ? implode(' / ', $personalityTags) : '';
        $likeCount = DB::table('favorites')
            ->where('cast_id', $castId)
            ->where('action_type', 3)
            ->count();

        $matchCount = 0;
        if (Schema::hasTable('shop_job_applications')) {
            $matchCount = (int) DB::table('shop_job_applications')
                ->where('cast_id', $castId)
                ->where('status', 4)
                ->count();
        }

        $bonusTotal = 0;
        if (Schema::hasTable('application_deposits') && Schema::hasTable('shop_job_applications')) {
            $bonusTotal = (int) DB::table('application_deposits')
                ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
                ->where('shop_job_applications.cast_id', $castId)
                ->whereNotNull('application_deposits.bonus_amount')
                ->sum('application_deposits.bonus_amount');
        }

        // 画像: cast_images (type=1) を id + url で取得（is_main を先に）
        $images = [];
        $castImages = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->get();
        foreach ($castImages as $img) {
            $images[] = [
                'id'  => $img->id,
                'url' => $this->assetPathForStored($img->image_path),
            ];
        }
        if (empty($images)) {
            $images[] = ['id' => null, 'url' => asset('assets/images/common/no-image.png')];
        }

        // レビュー（レビュー本文＋平均スコア）
        $reviewRows = DB::table('reviews')
            ->leftJoin('review_details', 'reviews.id', '=', 'review_details.review_id')
            ->where('reviews.cast_id', $castId)
            ->groupBy('reviews.id', 'reviews.contents', 'reviews.created_at')
            ->select(
                'reviews.id',
                'reviews.contents',
                'reviews.created_at',
                DB::raw('AVG(review_details.score) as avg_score')
            )
            ->get();

        $reviews = [];
        foreach ($reviewRows as $r) {
            $reviews[] = [
                'score' => $r->avg_score !== null ? (float) $r->avg_score : 0.0,
                'text'  => $r->contents ?? '',
            ];
        }

        // ひとこと：cast_posts テーブルから取得（店舗の catch と同様）
        $word = '';
        $appealUpdatedAt = null;
        if (Schema::hasTable('cast_posts') && Schema::hasColumn('cast_posts', 'body')) {
            $post = DB::table('cast_posts')->where('cast_id', $castId)->first();
            if ($post && isset($post->body) && $post->body !== null && trim((string) $post->body) !== '') {
                $word = trim((string) $post->body);
                $appealUpdatedAt = !empty($post->updated_at)
                    ? Carbon::parse($post->updated_at)->format('Y/m/d H:i')
                    : null;
            }
        }

        return [
            'id'               => $castRow->id,
            'nickname'         => $castRow->nickname ?? '',
            'name'             => $castRow->name ?? '',
            'age'              => $age,
            'birth_year'       => $birthday ? (string) $birthday->year : null,
            'birth_month'      => $birthday ? (string) $birthday->month : null,
            'birth_day'        => $birthday ? (string) $birthday->day : null,
            'images'           => $images,
            'img'              => $images[0]['url'] ?? null,
            'is_applied'       => true,
            'is_kept'          => true,
            'like_cnt'         => $likeCount,
            'match_cnt'        => $matchCount,
            'bonus_total'      => $bonusTotal,
            'zip'              => $castRow->zip ?? '',
            'pref'             => $castRow->pref ?? '',
            'city'             => $castRow->city ?? '',
            'addr1'            => trim(implode(' ', array_filter([(string) ($castRow->addr ?? ''), (string) ($castRow->building ?? '')]))),
            'height'           => $castRow->height,
            'weight'           => $castRow->weight,
            'bust'             => $castRow->bust,
            'waist'            => $castRow->waist,
            'hip'              => $castRow->hip,
            'word'             => $word,
            'pr'               => $castRow->pr ?? '',
            'intro'            => $castRow->pr ?? '',
            'appeal_updated_at'=> $appealUpdatedAt,
            'desired_job'      => $desiredJob,
            'my_field'         => $looksSummary,
            'my_inner_skills'  => $personalitySummary,
            'personality_type' => $this->resolvePersonalityType($castRow->personality_type ?? null),
            'looks_tags'       => $looksTags,
            'personality_tags' => $personalityTags,
            'memo_data'        => [
                'desired_job' => $desiredJob,
                'my_field' => $looksSummary,
                'my_inner_skills' => $personalitySummary,
                'shift_hope' => $shiftHope,
                'work_time' => $workTime,
                'night_work_exp' => $nightWorkExp,
                'current_job' => $castRow->profession ?? '',
            ],
            'shift_hope'       => $shiftHope,
            'work_time'        => $workTime,
            'work_time_label'  => $this->workTimeLabel($workTime),
            'current_job'      => $castRow->profession ?? '',
            'night_work_exp'   => $nightWorkExp,
            'night_work_label' => $nightWorkExp === 'yes' ? '有り' : '無し',
            'reviews'          => $reviews,
        ];
    }

    /** パスが / 始まりなら public 相対にし asset() で URL 化 */
    private function assetPath(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }
        $path = ltrim($path, '/');
        return asset($path);
    }

    /** 保存先パスを表示用URLに変換（uploads/ または public/ または / 始まり） */
    private function assetPathForStored(?string $path): string
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
        return $this->assetPath($path);
    }

    private function buildEmptyCast(): array
    {
        $images = [['id' => null, 'url' => asset('assets/images/common/no-image.png')]];
        return [
            'id'               => null,
            'nickname'         => '',
            'name'             => '',
            'age'              => null,
            'birth_year'       => null,
            'birth_month'      => null,
            'birth_day'        => null,
            'images'           => $images,
            'img'              => $images[0]['url'] ?? null,
            'is_applied'       => false,
            'is_kept'          => false,
            'like_cnt'         => 0,
            'match_cnt'        => 0,
            'bonus_total'      => 0,
            'zip'              => '',
            'pref'             => '',
            'city'             => '',
            'addr1'            => '',
            'height'           => null,
            'weight'           => null,
            'bust'             => null,
            'waist'            => null,
            'hip'              => null,
            'word'             => '',
            'pr'               => '',
            'intro'            => '',
            'appeal_updated_at'=> null,
            'desired_job'      => '',
            'my_field'         => '',
            'my_inner_skills'  => '',
            'personality_type' => '',
            'looks_tags'       => [],
            'personality_tags' => [],
            'memo_data'        => [
                'desired_job' => '',
                'my_field' => '',
                'my_inner_skills' => '',
                'shift_hope' => '',
                'work_time' => '',
                'night_work_exp' => '',
                'current_job' => '',
            ],
            'shift_hope'       => '',
            'work_time'        => '',
            'work_time_label'  => '',
            'current_job'      => '',
            'night_work_exp'   => '',
            'night_work_label' => '',
            'reviews'          => [],
        ];
    }

    private function currentCastId(): string
    {
        return (string) auth()->guard('member')->id();
    }

    private function syncCastImageOrder(string $castId, array $orderedIds): void
    {
        $existingImages = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->get(['id', 'image_path']);

        $existingIds = $existingImages->pluck('id')->map(fn ($id) => (int) $id)->all();
        $orderedIds = array_values(array_intersect($orderedIds, $existingIds));

        foreach ($existingIds as $imageId) {
            if (!in_array($imageId, $orderedIds, true)) {
                $orderedIds[] = $imageId;
            }
        }

        DB::transaction(function () use ($castId, $orderedIds, $existingImages) {
            DB::table('cast_images')
                ->where('cast_id', $castId)
                ->where('type', 1)
                ->update([
                    'is_main' => 0,
                    'updated_at' => now(),
                ]);

            foreach ($orderedIds as $index => $imageId) {
                DB::table('cast_images')
                    ->where('cast_id', $castId)
                    ->where('type', 1)
                    ->where('id', $imageId)
                    ->update([
                        'main_order' => $index,
                        'is_main' => $index === 0 ? 1 : 0,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('cast_profiles')
                ->where('cast_id', $castId)
                ->update([
                    'updated_at' => now(),
                ]);
        });
    }

    private function resolvePersonalityType(?string $columnType): string
    {
        $type = $columnType ?? '';

        return is_string($type) && preg_match('/^[LF][CP][IO][HR]$/', $type) ? $type : '';
    }

    /**
     * cast_tag_relations からタグ名を取得。なければ memo 内 id から補完。
     *
     * @param array<int, mixed> $memoTagIds
     * @return array<int, string>
     */
    private function getCastTagNamesByType(string $castId, string $tagType, array $memoTagIds = []): array
    {
        $tagTable = $this->resolveCastTagMasterTable();
        if ($tagTable === null) {
            return [];
        }

        $names = [];
        if (Schema::hasTable('cast_tag_relations')) {
            $tagTypes = array_values(array_unique(array_filter([$tagType, rtrim($tagType, 's')])));
            $names = DB::table('cast_tag_relations as r')
                ->join($tagTable . ' as t', 'r.tag_id', '=', 't.id')
                ->where('r.cast_id', $castId)
                ->whereIn('r.tag_type', $tagTypes)
                ->orderBy('t.id')
                ->pluck('t.name')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->values()
                ->all();
        }

        if ($names !== []) {
            return $names;
        }

        $ids = collect($memoTagIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
        if ($ids === []) {
            return [];
        }

        return DB::table($tagTable)
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveCastTagMasterTable(): ?string
    {
        if (Schema::hasTable('cast_tags')) {
            return 'cast_tags';
        }
        if (Schema::hasTable('tags')) {
            return 'tags';
        }
        return null;
    }

    private function shiftHopeLabel($shift): string
    {
        return match ((int) ($shift ?? 0)) {
            1 => '週1回出勤',
            2 => '週2回出勤',
            3 => '週3回以上',
            default => '',
        };
    }

    private function workTimeLabel(string $workTime): string
    {
        return match ($workTime) {
            'morning' => '朝〜昼',
            'day_night' => '夜',
            default => '',
        };
    }

    private function workTimeKeyFromShift($shift): string
    {
        return match ((int) ($shift ?? 0)) {
            1 => 'morning',
            2 => 'day_night',
            default => '',
        };
    }

    private function resolveDesiredJobByIndustries(string $castId, $fallbackIndustryId = null): string
    {
        $names = [];
        if (Schema::hasTable('cast_industry')) {
            $names = DB::table('cast_industry')
                ->join('industries', 'cast_industry.industry_id', '=', 'industries.id')
                ->where('cast_industry.cast_id', $castId)
                ->orderBy('cast_industry.industry_id')
                ->pluck('industries.name')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->values()
                ->all();
        }

        if ($names === []) {
            $single = (int) ($fallbackIndustryId ?? 0);
            if ($single > 0) {
                $name = DB::table('industries')->where('id', $single)->value('name');
                if ($name) {
                    $names = [trim((string) $name)];
                }
            }
        }

        return implode(' / ', array_values(array_unique($names)));
    }

    private function mapApplicationStatus(int $status): array
    {
        return match ($status) {
            2 => ['label' => '面談日調整中', 'class' => 'status-pending'],
            3 => ['label' => '面談日決定', 'class' => 'status-pending'],
            4 => ['label' => '採用', 'class' => 'status-paid'],
            5 => ['label' => '不採用', 'class' => 'status-ng'],
            6 => ['label' => '本採用', 'class' => 'status-paid'],
            7 => ['label' => '体験後不採用', 'class' => 'status-ng'],
            default => ['label' => 'やり取り中', 'class' => 'status-pending'],
        };
    }
}
