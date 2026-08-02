<?php

namespace App\Http\Controllers\Casts;

use App\Http\Concerns\ResolvesActor;
use App\Http\Controllers\Controller;
use App\Models\CastIdentityDocument;
use App\Services\DocumentReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Cast identity-verification controller.
 *
 * Extracted from Casts\MypageController (2026-08-02) to isolate the
 * identity-document 2-step submission workflow (upload -> submit -> pending -> approved).
 *
 * Handles:
 *   - GET  /cast/mypage/identity                (identity())
 *   - POST /cast/mypage/identity/upload         (uploadIdentity())     -> saves as STATUS_DRAFT
 *   - POST /cast/mypage/identity/submit         (submitForReview())    -> DRAFT -> PENDING
 *   - POST /cast/mypage/identity/remind         (identityRemind())     -> notifies admin (24h throttle)
 */
class IdentityController extends Controller
{
    use ResolvesActor;

    public function __construct(private readonly DocumentReviewService $documentReviewService) {}

    /** Identity page (view all category docs + current statuses). */
    public function identity()
    {
        $castId = $this->currentCastId();
        $identityData = $this->documentReviewService->getCastIdentityPageData($castId);

        return view('casts.mypage.identity', [
            'pageId' => 'mypage',
            'identityStatus' => $identityData['status'],
            'identityDocuments' => $identityData['documents'],
            'latestIdentityDocument' => $identityData['latest_document'],
            'isVerified' => $identityData['is_verified'] ?? false,
            'detectedPattern' => $identityData['pattern'] ?? 'photo',
            'categoryDocuments' => $identityData['category_documents'] ?? [],
            'allowedTypes' => $identityData['allowed_types'] ?? [],
            'typeLabels' => $identityData['type_labels'] ?? [],
            'identityRemindSentRecently' => $this->identityRemindSentRecently($castId),
        ]);
    }

    /** Ask admin to hurry the review; 1/day throttle. */
    public function identityRemind(Request $request)
    {
        $castId = $this->currentCastId();
        $identityData = $this->documentReviewService->getCastIdentityPageData($castId);

        if (($identityData['status'] ?? '') !== 'pending') {
            return back()->with('status', '審査中の書類がないため、催促は送信できません。');
        }
        if ($this->identityRemindSentRecently($castId)) {
            return back()->with('status', '催促は24時間に1回まで送信できます。すでに送信済みです。');
        }
        if (!Schema::hasTable('support_inquiries')) {
            return back()->with('status', '現在この機能は利用できません。問い合わせフォームをご利用ください。');
        }

        $email = '';
        if (Schema::hasColumn('casts', 'email')) {
            $email = (string) (DB::table('casts')->where('id', $castId)->value('email') ?? '');
        }

        DB::table('support_inquiries')->insert([
            'sender_type' => 'cast',
            'sender_id'   => $castId,
            'category'    => 'account',
            'email'       => $email !== '' ? $email : 'no-reply@misechoku.jp',
            'body'        => "【本人確認 承認催促】\nキャストID: {$castId}\n提出済みの本人確認書類の審査状況の確認をお願いします。",
            'status'      => 'new',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('status', '運営へ承認の催促を送信しました。審査完了までいましばらくお待ちください。');
    }

    /** Upload endpoint: saves as DRAFT (does NOT submit for review). */
    public function upload(Request $request)
    {
        $allowedCategories = [
            CastIdentityDocument::CATEGORY_PHOTO_ID,
            CastIdentityDocument::CATEGORY_NON_PHOTO_ID,
            CastIdentityDocument::CATEGORY_ADDRESS_PROOF,
        ];

        $request->validate([
            'category' => ['required', 'string', Rule::in($allowedCategories)],
            'type' => ['required', 'string'],
            'front_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            'back_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            'expired_at' => ['nullable', 'date'],
        ]);

        $category = (string) $request->input('category');
        $type = (string) $request->input('type');
        $allowedTypes = CastIdentityDocument::allowedTypesFor($category);
        if (!in_array($type, $allowedTypes, true)) {
            return response()->json([
                'success' => false,
                'message' => '選択した書類種別がカテゴリと一致しません。',
                'errors' => ['type' => ['カテゴリに対応する書類種別を選択してください。']],
            ], 422);
        }

        $castId = $this->currentCastId();
        $this->documentReviewService->uploadCastIdentityDocument(
            $castId,
            $type,
            $request->file('front_file'),
            $request->file('back_file'),
            $request->input('expired_at'),
            $category
        );

        return response()->json([
            'success' => true,
            'message' => 'アップロードが完了しました。「運営に提出する」を押すと審査が始まります。',
        ]);
    }

    /** Submit endpoint: DRAFT -> PENDING transition. */
    public function submitForReview(Request $request)
    {
        $allowedCategories = [
            CastIdentityDocument::CATEGORY_PHOTO_ID,
            CastIdentityDocument::CATEGORY_NON_PHOTO_ID,
            CastIdentityDocument::CATEGORY_ADDRESS_PROOF,
        ];

        $data = $request->validate([
            'category' => ['required', 'string', Rule::in($allowedCategories)],
        ]);

        try {
            $this->documentReviewService->requestCastIdentityReview(
                $this->currentCastId(),
                $data['category']
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: '提出に失敗しました。',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => '運営に提出しました。承認までお待ちください。',
        ]);
    }

    /** Whether a "please review" reminder has been sent in the past 24h. */
    private function identityRemindSentRecently(string $castId): bool
    {
        if (!Schema::hasTable('support_inquiries')) {
            return false;
        }
        return DB::table('support_inquiries')
            ->where('sender_type', 'cast')
            ->where('sender_id', $castId)
            ->where('body', 'like', '【本人確認 承認催促】%')
            ->where('created_at', '>=', now()->subDay())
            ->exists();
    }
}
