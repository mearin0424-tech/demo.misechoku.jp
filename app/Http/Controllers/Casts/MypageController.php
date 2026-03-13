<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    /**
     * キャスト用マイページ（プロフィール確認＝shop/castprofileview と同じ内容）
     */
    public function index()
    {
        $cast = $this->getCastFromDatabase($this->currentCastId());
        $reviewCount = count($cast['reviews']);
        $reviewAvg = $reviewCount > 0
            ? round(array_sum(array_column($cast['reviews'], 'score')) / $reviewCount, 1)
            : 0;
        // プロフィール画面にはレビュー本文を出さず、★カードから一覧へ遷移
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
            'review_avg'   => $reviewAvg,
            'review_count' => $reviewCount,
            'subImages'    => $subImages,
        ]);
    }

    /**
     * 採用状況
     */
    public function employment()
    {
        return view('casts.mypage.employment', [
            'pageId' => 'mypage',
        ]);
    }

    /**
     * 請求・入金管理
     */
    public function payment()
    {
        $castId = $this->currentCastId();

        $deposit = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shops', 'shop_jobs.shop_id', '=', 'shops.id')
            ->where('shop_job_applications.cast_id', $castId)
            ->select(
                'application_deposits.*',
                'shop_job_applications.result_date',
                'shop_jobs.hourly_wage_regular',
                'shops.id as shop_id'
            )
            ->orderByDesc('application_deposits.id')
            ->first();

        $payments = [];
        $flow = $this->buildDepositFlowStateFromDb($deposit);

        if ($deposit) {
            $statusLabel = match ((int)$deposit->status) {
                1 => '申請中',
                2 => '店舗確認中',
                3 => '運営請求中',
                4 => '店舗入金報告済',
                5 => '店舗入金確認済',
                6 => 'キャスト振込済',
                7 => '完了',
                default => '不明',
            };
            $statusClass = in_array((int)$deposit->status, [6, 7], true) ? 'status-paid' : 'status-pending';

            $payments[] = [
                'title'        => 'ボーナス入金申請',
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'date'         => $deposit->created_at ? Carbon::parse($deposit->created_at)->format('Y/m/d H:i') : null,
                'link'         => null,
            ];
        }

        return view('casts.mypage.payment', [
            'pageId'       => 'mypage',
            'payments'     => $payments,
            'depositFlow' => $flow,
        ]);
    }

    /**
     * 本人確認画面
     */
    public function identity()
    {
        $castId = $this->currentCastId();
        $raw = DB::table('casts')->where('id', $castId)->value('identity_status');

        // 1:未提出, 2:未承認, 3:承認済み
        $status = match ((int)($raw ?? 1)) {
            1 => 'not_submitted',
            2 => 'pending',
            3 => 'approved',
            default => 'not_submitted',
        };

        return view('casts.mypage.identity', [
            'pageId' => 'mypage',
            'identityStatus' => $status,
        ]);
    }

    /**
     * 本人確認書類アップロード（デモ用）
     */
    public function uploadIdentity(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ]);

        // 実装ではストレージとDBに保存する想定。
        // デモでは casts.identity_status を「提出済み（未承認）」に更新。
        $castId = $this->currentCastId();
        DB::table('casts')
            ->where('id', $castId)
            ->update(['identity_status' => 2, 'updated_at' => now()]);

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

        $dir = public_path('uploads/casts/gallery');
        File::ensureDirectoryExists($dir);
        $file = $request->file('image');
        $name = $file->hashName();
        $file->move($dir, $name);
        $path = 'uploads/casts/gallery/' . $name;

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

        if ($isMain) {
            DB::table('cast_profiles')->where('cast_id', $castId)->update([
                'main_image_path' => $path,
                'updated_at'      => now(),
            ]);
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

        if (!empty($row->is_main)) {
            $next = DB::table('cast_images')
                ->where('cast_id', $castId)
                ->where('type', 1)
                ->orderBy('main_order')
                ->orderBy('id')
                ->first();
            $mainPath = $next ? $next->image_path : null;
            DB::table('cast_profiles')->where('cast_id', $castId)->update([
                'main_image_path' => $mainPath,
                'updated_at'      => now(),
            ]);
            if ($next) {
                DB::table('cast_images')->where('id', $next->id)->update(['is_main' => 1, 'updated_at' => now()]);
            }
        }

        return response()->json(['success' => true, 'message' => '画像を削除しました']);
    }

    /**
     * キャスト側：入金申請（ボーナス条件達成後に押す想定）
     */
    public function requestDeposit(\Illuminate\Http\Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step < 1) {
            session(['deposit_flow_step' => 1]);
        }

        return redirect()->route('cast.mypage.payment')->with('status', '入金申請を受け付けました。店舗・運営の確認をお待ちください。');
    }

    /**
     * キャスト側：入金完了確認（最終確認）
     */
    public function confirmDeposit(\Illuminate\Http\Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 5) {
            session(['deposit_flow_step' => 6]);
        }

        return redirect()->route('cast.mypage.payment')->with('status', '入金を確認しました。ありがとうございました。');
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
        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'account_type'   => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_name'   => 'required|string|max:100',
        ]);

        return response()->json([
            'success' => true,
            'message' => '口座情報を保存しました。（デモ環境ではDB保存は行っていません）',
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
     * キャスト・プロフィール・レビューをDBから取得し、画面用配列に整形
     */
    private function getCastFromDatabase(string $castId): array
    {
        $this->cleanupStaleMainImagePath($castId);

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
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.height',
                'cast_profiles.weight',
                'cast_profiles.bust',
                'cast_profiles.waist',
                'cast_profiles.hip',
                'cast_profiles.shift',
                'cast_profiles.profession',
                'cast_profiles.exp',
                'cast_profiles.pr',
                'cast_profiles.memo',
                'cast_profiles.main_image_path'
            )
            ->first();

        if (!$castRow) {
            // データ不在時は空のモック相当を返す
            return $this->buildEmptyCast();
        }

        $birthday = $castRow->birthday ? Carbon::parse($castRow->birthday) : null;
        $age = $birthday ? $birthday->age : null;
        $memo = $this->decodeProfileMemo($castRow->memo ?? null);
        $shiftHope = $memo['shift_hope'] ?? $this->shiftHopeLabel($castRow->shift);
        $workTime = $memo['work_time'] ?? '';
        $nightWorkExp = $memo['night_work_exp'] ?? ((int) ($castRow->exp ?? 0) === 1 ? 'yes' : 'none');

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
            'like_cnt'         => 0,
            'pref'             => $castRow->pref ?? '',
            'city'             => $castRow->city ?? '',
            'height'           => $castRow->height,
            'weight'           => $castRow->weight,
            'bust'             => $castRow->bust,
            'waist'            => $castRow->waist,
            'hip'              => $castRow->hip,
            'word'             => $castRow->pr ? mb_strimwidth($castRow->pr, 0, 80, '...') : '',
            'pr'               => $castRow->pr ?? '',
            'intro'            => $castRow->pr ?? '',
            'desired_job'      => $memo['desired_job'] ?? '',
            'my_field'         => $memo['my_field'] ?? '',
            'my_inner_skills'  => $memo['my_inner_skills'] ?? '',
            'personality_type' => '',
            'shift_hope'       => $shiftHope,
            'work_time'        => $workTime,
            'work_time_label'  => $this->workTimeLabel($workTime),
            'current_job'      => $memo['current_job'] ?? ($castRow->profession ?? ''),
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
            'pref'             => '',
            'city'             => '',
            'height'           => null,
            'weight'           => null,
            'bust'             => null,
            'waist'            => null,
            'hip'              => null,
            'word'             => '',
            'pr'               => '',
            'intro'            => '',
            'desired_job'      => '',
            'my_field'         => '',
            'my_inner_skills'  => '',
            'personality_type' => '',
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

    private function cleanupStaleMainImagePath(string $castId): void
    {
        $hasImages = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->exists();

        if (!$hasImages) {
            DB::table('cast_profiles')
                ->where('cast_id', $castId)
                ->whereNotNull('main_image_path')
                ->update([
                    'main_image_path' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function decodeProfileMemo(?string $memo): array
    {
        if (empty($memo)) {
            return [];
        }

        $decoded = json_decode($memo, true);

        return is_array($decoded) ? $decoded : [];
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
            'morning' => '朝',
            'day_night' => '昼or夜',
            default => '',
        };
    }
}
