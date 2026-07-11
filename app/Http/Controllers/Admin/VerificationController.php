<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CastIdentityDocument;
use App\Models\ShopLicenseDocument;
use App\Services\AdminOperationLogService;
use App\Services\DocumentReviewService;
use App\Services\MessageTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function __construct(
        private readonly DocumentReviewService $documentReviewService,
        private readonly MessageTemplateService $messageTemplateService,
        private readonly AdminOperationLogService $opLog,
    )
    {
    }

    /**
     * 本人確認・書類提出ステータス一覧
     */
    public function index()
    {
        $verificationData = $this->documentReviewService->getAdminVerificationData();

        return view('admin.verification.index', [
            'castDocuments' => $verificationData['cast_documents'],
            'shopDocuments' => $verificationData['shop_documents'],
            'summary' => $verificationData['summary'],
            'rejectTemplates' => $this->messageTemplateService->getGroupedTemplates([
                'document_reject_cast',
                'document_reject_shop',
            ]),
        ]);
    }

    /**
     * 一覧に戻る際、直前のフィルタ状態（focus / cast_status 等のクエリ）を保持する。
     * リファラが verification 画面ならそこへ、そうでなければ素の一覧へ。
     */
    private function backToIndex(string $flash)
    {
        $referer = (string) request()->headers->get('referer', '');
        if ($referer !== '' && str_contains($referer, '/admin/verification')) {
            return redirect($referer)->with('status', $flash);
        }
        return redirect()->route('admin.verification.index')->with('status', $flash);
    }

    /**
     * キャスト本人確認の承認
     */
    public function approveCast(Request $request, int $document)
    {
        $this->documentReviewService->approveCastDocument($document);
        $this->opLog->record('verification.cast.approve', 'cast_identity_document', (string) $document, '本人確認書類を承認');

        return $this->backToIndex('キャストの本人確認書類を承認しました。本人へ通知済みです。');
    }

    public function rejectCast(Request $request, int $document)
    {
        $data = $request->validate([
            'ng_reason' => 'required|string|max:2000',
        ]);

        $this->documentReviewService->rejectCastDocument($document, (string) $data['ng_reason']);
        $this->opLog->record('verification.cast.reject', 'cast_identity_document', (string) $document, '本人確認書類を差戻し', [
            'ng_reason' => (string) $data['ng_reason'],
        ]);

        return $this->backToIndex('キャストの本人確認書類を却下しました。差戻し理由を本人へ通知済みです。');
    }

    /**
     * 店舗の書類提出の承認
     */
    public function approveShopDocument(Request $request, int $document)
    {
        $this->documentReviewService->approveShopDocument($document);
        $this->opLog->record('verification.shop.approve', 'shop_license_document', (string) $document, '店舗書類を承認');

        return $this->backToIndex('店舗提出書類を承認しました。店舗へ通知済みです。');
    }

    public function rejectShopDocument(Request $request, int $document)
    {
        $data = $request->validate([
            'ng_reason' => 'required|string|max:2000',
        ]);

        $this->documentReviewService->rejectShopDocument($document, (string) $data['ng_reason']);
        $this->opLog->record('verification.shop.reject', 'shop_license_document', (string) $document, '店舗書類を差戻し', [
            'ng_reason' => (string) $data['ng_reason'],
        ]);

        return $this->backToIndex('店舗提出書類を却下しました。差戻し理由を店舗へ通知済みです。');
    }

    /**
     * 運営：キャスト本人確認書類の完全削除（保持期間ポリシーに基づく手動パージ）。
     * private ディスクの実ファイルも一緒に消す。
     */
    public function purgeCast(Request $request, int $document)
    {
        $this->documentReviewService->purgeCastDocument($document);
        $this->opLog->record('verification.cast.purge', 'cast_identity_document', (string) $document, '本人確認書類を完全削除');

        return $this->backToIndex('キャストの本人確認書類を完全削除しました。');
    }

    /**
     * 運営：店舗書類の完全削除（保持期間ポリシーに基づく手動パージ）。
     */
    public function purgeShopDocument(Request $request, int $document)
    {
        $this->documentReviewService->purgeShopDocument($document);
        $this->opLog->record('verification.shop.purge', 'shop_license_document', (string) $document, '店舗書類を完全削除');

        return $this->backToIndex('店舗提出書類を完全削除しました。');
    }

    /**
     * 運営：キャスト本人確認書類のファイル配信。
     * Web 直アクセス不可の private ディスクから、認証＋権限ミドルウェア通過後に限り配信する。
     */
    public function viewCastFile(int $document, string $side)
    {
        if (!in_array($side, ['front', 'back'], true)) {
            abort(404);
        }
        $doc = CastIdentityDocument::query()->findOrFail($document);
        $path = $side === 'back' ? $doc->image_path_back : $doc->image_path_front;
        return $this->streamDocument($path);
    }

    /**
     * 運営：店舗書類のファイル配信。
     */
    public function viewShopFile(int $document)
    {
        $doc = ShopLicenseDocument::query()->findOrFail($document);
        return $this->streamDocument($doc->image_path);
    }

    private function streamDocument(?string $storedPath)
    {
        if (empty($storedPath)) {
            abort(404, 'ファイルが見つかりません。');
        }
        $resolved = $this->documentReviewService->resolveDocumentDiskPath($storedPath);
        if ($resolved === null) {
            abort(404, 'ファイルが見つかりません。');
        }
        [$disk, $relative] = $resolved;
        if (!Storage::disk($disk)->exists($relative)) {
            abort(404, 'ファイルが見つかりません。');
        }
        $absolute = Storage::disk($disk)->path($relative);
        $mime = @mime_content_type($absolute) ?: 'application/octet-stream';
        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename*=UTF-8\'\'' . rawurlencode(basename($relative)),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

