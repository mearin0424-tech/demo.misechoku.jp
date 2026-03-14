<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DocumentReviewService;
use App\Services\MessageTemplateService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private readonly DocumentReviewService $documentReviewService,
        private readonly MessageTemplateService $messageTemplateService
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
     * キャスト本人確認の承認
     */
    public function approveCast(Request $request, int $document)
    {
        $this->documentReviewService->approveCastDocument($document);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', 'キャストの本人確認書類を承認しました。');
    }

    public function rejectCast(Request $request, int $document)
    {
        $data = $request->validate([
            'ng_reason' => 'required|string|max:2000',
        ]);

        $this->documentReviewService->rejectCastDocument($document, (string) $data['ng_reason']);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', 'キャストの本人確認書類を却下しました。');
    }

    /**
     * 店舗の書類提出の承認
     */
    public function approveShopDocument(Request $request, int $document)
    {
        $this->documentReviewService->approveShopDocument($document);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', '店舗提出書類を承認しました。');
    }

    public function rejectShopDocument(Request $request, int $document)
    {
        $data = $request->validate([
            'ng_reason' => 'required|string|max:2000',
        ]);

        $this->documentReviewService->rejectShopDocument($document, (string) $data['ng_reason']);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', '店舗提出書類を却下しました。');
    }
}

