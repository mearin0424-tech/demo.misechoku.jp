<?php

namespace App\Services;

use App\Models\CastIdentityDocument;
use App\Models\ShopLicenseDocument;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DocumentReviewService
{
    public function getCastIdentityPageData(string $castId): array
    {
        $documents = CastIdentityDocument::query()
            ->where('cast_id', $castId)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $latestDocument = $documents->first();

        return [
            'status' => $this->castStatusKey($documents),
            'documents' => $documents
                ->map(fn (CastIdentityDocument $document) => $this->mapCastDocument($document))
                ->all(),
            'latest_document' => $latestDocument ? $this->mapCastDocument($latestDocument) : null,
        ];
    }

    public function uploadCastIdentityDocument(
        string $castId,
        string $type,
        UploadedFile $frontFile,
        ?UploadedFile $backFile = null,
        ?string $expiredAt = null
    ): CastIdentityDocument {
        $frontPath = $frontFile->store('public/casts/identity');
        $backPath = $backFile?->store('public/casts/identity');

        $document = CastIdentityDocument::query()->updateOrCreate(
            [
                'cast_id' => $castId,
                'type' => $type,
            ],
            [
                'image_path_front' => $frontPath,
                'image_path_back' => $backPath,
                'status' => CastIdentityDocument::STATUS_PENDING,
                'ng_reason' => null,
                'expired_at' => $expiredAt ?: null,
                'approved_at' => null,
            ]
        );

        $this->syncCastLegacyStatus($castId);

        return $document->fresh();
    }

    public function getShopLicensePageData(string $shopId): array
    {
        $definitions = [
            ['key' => 'business', 'name' => '営業許可証'],
            ['key' => 'entertainment', 'name' => '風営許可証'],
        ];

        if (!Schema::hasTable('shop_license_documents')) {
            $documents = [];
            foreach ($definitions as $def) {
                $status = 'not_submitted';
                $documents[] = [
                    'key' => $def['key'],
                    'name' => $def['name'],
                    'status' => $status,
                    'status_label' => $this->shopDocumentStatusLabel($status),
                    'record' => null,
                ];
            }

            return [
                'documents' => $documents,
                'all_approved' => false,
            ];
        }

        $byType = ShopLicenseDocument::query()
            ->where('shop_id', $shopId)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->keyBy('type');

        $mapped = [];
        foreach ($definitions as $def) {
            $type = $def['key'];
            /** @var ShopLicenseDocument|null $document */
            $document = $byType->get($type);
            $status = $this->shopStatusKey($document);
            $mapped[] = [
                'key' => $type,
                'name' => $def['name'],
                'status' => $status,
                'status_label' => $this->shopDocumentStatusLabel($status),
                'record' => $document ? $this->mapShopDocument($document) : null,
            ];
        }

        return [
            'documents' => $mapped,
            'all_approved' => collect($mapped)->every(fn (array $row) => $row['status'] === 'approved'),
        ];
    }

    /**
     * 営業・風営の両書類が承認済みか（求人の公開可否に利用）。
     */
    public function shopLicenseFullyApproved(string $shopId): bool
    {
        return $this->getShopLicensePageData($shopId)['all_approved'];
    }

    /**
     * 店舗ポータルヘッダー「やること」用（未提出・差し戻しのみ）。
     *
     * @return array<int, array{text: string}>
     */
    public function getShopPortalTodoMessages(string $shopId): array
    {
        $data = $this->getShopLicensePageData($shopId);
        $out = [];
        foreach ($data['documents'] as $doc) {
            if (($doc['status'] ?? '') === 'not_submitted') {
                $out[] = [
                    'text' => ($doc['name'] ?? '許可証') . 'の提出が未完了です。マイページの Licenses からアップロードしてください。',
                ];
                continue;
            }
            if (($doc['status'] ?? '') === 'rejected') {
                $reason = trim((string) ($doc['record']['ng_reason'] ?? ''));
                $suffix = $reason !== '' ? '（差し戻し理由: ' . $reason . '）' : '';
                $out[] = [
                    'text' => ($doc['name'] ?? '許可証') . 'が差し戻されています' . $suffix . '。内容を確認のうえ再提出してください。',
                ];
            }
        }

        return $out;
    }

    /**
     * マイページのカード・モーダルで共通利用する表示ラベル。
     */
    private function shopDocumentStatusLabel(string $statusKey): string
    {
        return match ($statusKey) {
            'approved' => '承認済み',
            'rejected' => '差し戻し',
            'pending' => '審査中',
            'draft' => 'アップロード済み',
            default => '未提出',
        };
    }

    public function uploadShopLicenseDocument(
        string $shopId,
        string $type,
        UploadedFile $file,
        ?string $expiredAt = null
    ): ShopLicenseDocument {
        // storage/app/public 配下（/storage シンボリックリンクで公開可）。旧データは public/ 接頭辞で保存されていた。
        $path = $file->store('shops/documents', 'public');

        $document = ShopLicenseDocument::query()->updateOrCreate(
            [
                'shop_id' => $shopId,
                'type' => $type,
            ],
            [
                'image_path' => $path,
                'status' => ShopLicenseDocument::STATUS_DRAFT,
                'ng_reason' => null,
                'expired_at' => $expiredAt ?: null,
                'approved_at' => null,
            ]
        );

        $this->syncShopLegacyStatus($shopId);

        return $document->fresh();
    }

    public function requestShopDocumentReview(string $shopId, string $type, ?string $expiredAt = null): ShopLicenseDocument
    {
        $document = ShopLicenseDocument::query()
            ->where('shop_id', $shopId)
            ->where('type', $type)
            ->firstOrFail();

        if (empty($document->image_path)) {
            throw new \RuntimeException('ファイルが未アップロードです。');
        }
        if ((int) $document->status === ShopLicenseDocument::STATUS_APPROVED) {
            throw new \RuntimeException('承認済みのため再審査依頼はできません。差し替え後に審査依頼してください。');
        }
        if ((int) $document->status === ShopLicenseDocument::STATUS_PENDING) {
            throw new \RuntimeException('すでに審査依頼中です。');
        }
        if ($type === 'business' && empty($expiredAt) && !$document->expired_at) {
            throw new \RuntimeException('営業許可証の有効期限を入力してください。');
        }

        $document->update([
            'status' => ShopLicenseDocument::STATUS_PENDING,
            'ng_reason' => null,
            'approved_at' => null,
            'expired_at' => $type === 'business' ? ($expiredAt ?: optional($document->expired_at)->format('Y-m-d')) : null,
        ]);

        $this->syncShopLegacyStatus($shopId);

        return $document->fresh();
    }

    public function withdrawShopDocumentReview(string $shopId, string $type): ShopLicenseDocument
    {
        $document = ShopLicenseDocument::query()
            ->where('shop_id', $shopId)
            ->where('type', $type)
            ->firstOrFail();
        if (!in_array((int) $document->status, [ShopLicenseDocument::STATUS_PENDING, ShopLicenseDocument::STATUS_APPROVED], true)) {
            throw new \RuntimeException('提出済みの書類のみ取り下げできます。');
        }

        $document->update([
            'status' => ShopLicenseDocument::STATUS_DRAFT,
            'ng_reason' => null,
            'approved_at' => null,
        ]);

        $this->syncShopLegacyStatus($shopId);

        return $document->fresh();
    }

    public function getAdminVerificationData(): array
    {
        $castDocuments = CastIdentityDocument::query()
            ->leftJoin('cast_profiles', 'cast_identity_documents.cast_id', '=', 'cast_profiles.cast_id')
            ->select('cast_identity_documents.*', 'cast_profiles.nickname', 'cast_profiles.name')
            ->orderByRaw('CASE WHEN cast_identity_documents.status = 1 THEN 0 WHEN cast_identity_documents.status = 3 THEN 1 ELSE 2 END')
            ->orderByDesc('cast_identity_documents.updated_at')
            ->get()
            ->map(fn (object $document) => $this->mapCastDocumentRecord($document))
            ->all();

        $shopDocuments = ShopLicenseDocument::query()
            ->leftJoin('shop_profiles', 'shop_license_documents.shop_id', '=', 'shop_profiles.shop_id')
            ->whereIn('shop_license_documents.status', [
                ShopLicenseDocument::STATUS_PENDING,
                ShopLicenseDocument::STATUS_APPROVED,
                ShopLicenseDocument::STATUS_REJECTED,
            ])
            ->select('shop_license_documents.*', 'shop_profiles.shop_name')
            ->orderByRaw('CASE WHEN shop_license_documents.status = 1 THEN 0 WHEN shop_license_documents.status = 3 THEN 1 ELSE 2 END')
            ->orderByDesc('shop_license_documents.updated_at')
            ->get()
            ->map(fn (object $document) => $this->mapShopDocumentRecord($document))
            ->all();

        return [
            'cast_documents' => $castDocuments,
            'shop_documents' => $shopDocuments,
            'summary' => [
                'cast_pending' => collect($castDocuments)->where('status_code', CastIdentityDocument::STATUS_PENDING)->count(),
                'shop_pending' => collect($shopDocuments)->where('status_code', ShopLicenseDocument::STATUS_PENDING)->count(),
            ],
        ];
    }

    public function approveCastDocument(int $documentId): void
    {
        $document = CastIdentityDocument::query()->findOrFail($documentId);
        $document->update([
            'status' => CastIdentityDocument::STATUS_APPROVED,
            'ng_reason' => null,
            'approved_at' => now(),
        ]);

        $this->syncCastLegacyStatus($document->cast_id);
    }

    public function rejectCastDocument(int $documentId, string $reason): void
    {
        $document = CastIdentityDocument::query()->findOrFail($documentId);
        $document->update([
            'status' => CastIdentityDocument::STATUS_REJECTED,
            'ng_reason' => $reason,
            'approved_at' => null,
        ]);

        $this->syncCastLegacyStatus($document->cast_id);
    }

    public function approveShopDocument(int $documentId): void
    {
        $document = ShopLicenseDocument::query()->findOrFail($documentId);
        $document->update([
            'status' => ShopLicenseDocument::STATUS_APPROVED,
            'ng_reason' => null,
            'approved_at' => now(),
        ]);

        $this->syncShopLegacyStatus($document->shop_id);
    }

    public function rejectShopDocument(int $documentId, string $reason): void
    {
        $document = ShopLicenseDocument::query()->findOrFail($documentId);
        $document->update([
            'status' => ShopLicenseDocument::STATUS_REJECTED,
            'ng_reason' => $reason,
            'approved_at' => null,
        ]);

        $this->syncShopLegacyStatus($document->shop_id);
    }

    public function getDashboardTasks(): array
    {
        $verificationData = $this->getAdminVerificationData();

        $castTasks = collect($verificationData['cast_documents'])
            ->whereIn('status_code', [CastIdentityDocument::STATUS_PENDING, CastIdentityDocument::STATUS_REJECTED])
            ->map(fn (array $document) => [
                'id' => 'cast-doc-' . $document['id'],
                'category' => '本人確認',
                'target' => $document['target_name'],
                'type' => 'キャスト',
                'status' => $document['status_label'],
                'date' => $document['updated_at_label'],
                'urgency' => $document['status_code'] === CastIdentityDocument::STATUS_PENDING ? 'high' : 'critical',
                'action' => '審査する',
                'cat_id' => 'kyc',
                'amount' => null,
                'url' => route('admin.verification.index', [
                    'cast_status' => 'pending',
                    'focus' => 'cast',
                ]),
            ]);

        $shopTasks = collect($verificationData['shop_documents'])
            ->whereIn('status_code', [ShopLicenseDocument::STATUS_PENDING, ShopLicenseDocument::STATUS_REJECTED])
            ->map(fn (array $document) => [
                'id' => 'shop-doc-' . $document['id'],
                'category' => '書類審査',
                'target' => $document['target_name'],
                'type' => '店舗',
                'status' => $document['status_label'],
                'date' => $document['updated_at_label'],
                'urgency' => $document['status_code'] === ShopLicenseDocument::STATUS_PENDING ? 'normal' : 'high',
                'action' => '書類確認',
                'cat_id' => 'doc',
                'amount' => null,
                'url' => route('admin.verification.index', [
                    'shop_status' => 'pending',
                    'focus' => 'shop',
                ]),
            ]);

        return $castTasks
            ->concat($shopTasks)
            ->values()
            ->all();
    }

    private function syncCastLegacyStatus(string $castId): void
    {
        $documents = CastIdentityDocument::query()->where('cast_id', $castId)->get();
        $status = match ($this->castStatusKey($documents)) {
            'approved' => 3,
            'not_submitted' => 1,
            default => 2,
        };

        DB::table('casts')
            ->where('id', $castId)
            ->update([
                'identity_status' => $status,
                'updated_at' => now(),
            ]);
    }

    private function syncShopLegacyStatus(string $shopId): void
    {
        $documents = ShopLicenseDocument::query()
            ->where('shop_id', $shopId)
            ->get()
            ->keyBy('type');

        $business = $documents->get('business');
        $entertainment = $documents->get('entertainment');

        $businessStatus = $this->legacyShopTypeStatus($business);
        $entertainmentStatus = $this->legacyShopTypeStatus($entertainment);
        $overallStatus = $businessStatus === 3 && $entertainmentStatus === 3
            ? 3
            : (($business || $entertainment) ? 2 : 1);

        DB::table('shops')
            ->where('id', $shopId)
            ->update([
                'license_status' => $overallStatus,
                'business_license_status' => $businessStatus,
                'entertainment_license_status' => $entertainmentStatus,
                'updated_at' => now(),
            ]);
    }

    private function legacyShopTypeStatus(?ShopLicenseDocument $document): int
    {
        if (!$document) {
            return 1;
        }

        if ((int) $document->status === ShopLicenseDocument::STATUS_APPROVED) {
            return 3;
        }
        if ((int) $document->status === ShopLicenseDocument::STATUS_PENDING) {
            return 2;
        }

        return 1;
    }

    private function castStatusKey(Collection $documents): string
    {
        if ($documents->isEmpty()) {
            return 'not_submitted';
        }

        if ($documents->contains(fn (CastIdentityDocument $document) => $document->status === CastIdentityDocument::STATUS_PENDING)) {
            return 'pending';
        }

        if ($documents->contains(fn (CastIdentityDocument $document) => $document->status === CastIdentityDocument::STATUS_REJECTED)) {
            return 'rejected';
        }

        return 'approved';
    }

    private function shopStatusKey(?ShopLicenseDocument $document): string
    {
        if (!$document) {
            return 'not_submitted';
        }

        return match ((int) $document->status) {
            ShopLicenseDocument::STATUS_DRAFT => 'draft',
            ShopLicenseDocument::STATUS_APPROVED => 'approved',
            ShopLicenseDocument::STATUS_REJECTED => 'rejected',
            default => 'pending',
        };
    }

    private function mapCastDocument(CastIdentityDocument $document): array
    {
        return [
            'id' => $document->id,
            'type' => $document->type,
            'type_label' => $this->castTypeLabel($document->type),
            'status_code' => (int) $document->status,
            'status_key' => match ((int) $document->status) {
                CastIdentityDocument::STATUS_APPROVED => 'approved',
                CastIdentityDocument::STATUS_REJECTED => 'rejected',
                default => 'pending',
            },
            'status_label' => $this->statusLabel((int) $document->status),
            'ng_reason' => $document->ng_reason,
            'expired_at' => optional($document->expired_at)->format('Y-m-d'),
            'approved_at' => optional($document->approved_at)->format('Y-m-d H:i'),
            'updated_at_label' => optional($document->updated_at)->format('Y-m-d H:i'),
            'front_url' => $this->documentUrl($document->image_path_front),
            'back_url' => $this->documentUrl($document->image_path_back),
        ];
    }

    private function mapShopDocument(ShopLicenseDocument $document): array
    {
        $expiringSoon = $this->isBusinessLicenseExpiringSoon($document);

        $imagePath = (string) ($document->image_path ?? '');

        return [
            'id' => $document->id,
            'type' => $document->type,
            'type_label' => $this->shopTypeLabel($document->type),
            'status_code' => (int) $document->status,
            'status_key' => $this->shopStatusKey($document),
            'status_label' => $this->statusLabel((int) $document->status),
            'ng_reason' => $document->ng_reason,
            'expired_at' => (function () use ($document) {
                if (!$document->expired_at) {
                    return '';
                }
                try {
                    return \Illuminate\Support\Carbon::parse($document->expired_at)->format('Y-m-d');
                } catch (\Throwable) {
                    return '';
                }
            })(),
            'approved_at' => optional($document->approved_at)->format('Y-m-d H:i'),
            'updated_at_label' => optional($document->updated_at)->format('Y-m-d H:i'),
            'expiring_soon' => $expiringSoon,
            'expiration_notice_label' => $expiringSoon ? '更新期限半年以内' : null,
            'can_request_review' => in_array((int) $document->status, [ShopLicenseDocument::STATUS_DRAFT, ShopLicenseDocument::STATUS_REJECTED], true),
            'can_withdraw_review' => in_array((int) $document->status, [ShopLicenseDocument::STATUS_PENDING, ShopLicenseDocument::STATUS_APPROVED], true),
            'file_is_pdf' => $imagePath !== '' && str_ends_with(strtolower($imagePath), '.pdf'),
            'file_name' => $imagePath !== '' ? basename($imagePath) : '',
            // マイページは認証付きルートで表示（シンボリックリンク未作成環境でも閲覧可）
            'file_url' => route('shop.mypage.documents.show', ['type' => $document->type]),
        ];
    }

    private function isBusinessLicenseExpiringSoon(ShopLicenseDocument $document): bool
    {
        if ($document->type !== 'business' || !$document->expired_at) {
            return false;
        }

        $today = Carbon::today();
        $expiredAt = Carbon::parse($document->expired_at)->startOfDay();
        $alertStart = $expiredAt->copy()->subMonthsNoOverflow(6);

        return $today->gte($alertStart) && $today->lte($expiredAt);
    }

    private function mapCastDocumentRecord(object $document): array
    {
        $name = trim((string) ($document->nickname ?: $document->name ?: $document->cast_id));

        return [
            'id' => (int) $document->id,
            'target_id' => $document->cast_id,
            'target_name' => $name !== '' ? $name : $document->cast_id,
            'type' => $document->type,
            'type_label' => $this->castTypeLabel($document->type),
            'status_code' => (int) $document->status,
            'status_key' => $this->statusKey((int) $document->status),
            'status_label' => $this->statusLabel((int) $document->status),
            'sort_rank' => $this->statusSortRank((int) $document->status),
            'ng_reason' => $document->ng_reason,
            'expired_at' => $document->expired_at,
            'approved_at' => $document->approved_at,
            'expired_at_label' => $document->expired_at ? date('Y-m-d', strtotime((string) $document->expired_at)) : null,
            'approved_at_label' => $this->formatDateTime($document->approved_at),
            'updated_at_label' => $this->formatDateTime($document->updated_at),
            'updated_at_sort' => $document->updated_at ? strtotime((string) $document->updated_at) : 0,
            'front_url' => $this->documentUrl($document->image_path_front),
            'back_url' => $this->documentUrl($document->image_path_back),
        ];
    }

    private function mapShopDocumentRecord(object $document): array
    {
        $expiryFilterKey = $this->shopDocumentExpiryFilterKey(
            (string) ($document->type ?? ''),
            $document->expired_at ?? null
        );

        return [
            'id' => (int) $document->id,
            'target_id' => $document->shop_id,
            'target_name' => $document->shop_name ?: $document->shop_id,
            'type' => $document->type,
            'type_label' => $this->shopTypeLabel($document->type),
            'status_code' => (int) $document->status,
            'status_key' => $this->statusKey((int) $document->status),
            'status_label' => $this->statusLabel((int) $document->status),
            'sort_rank' => $this->statusSortRank((int) $document->status),
            'ng_reason' => $document->ng_reason,
            'expired_at' => $document->expired_at,
            'approved_at' => $document->approved_at,
            'expired_at_label' => $document->expired_at ? date('Y-m-d', strtotime((string) $document->expired_at)) : null,
            'approved_at_label' => $this->formatDateTime($document->approved_at),
            'updated_at_label' => $this->formatDateTime($document->updated_at),
            'updated_at_sort' => $document->updated_at ? strtotime((string) $document->updated_at) : 0,
            'expiry_filter_key' => $expiryFilterKey,
            'file_url' => $this->documentUrl($document->image_path),
        ];
    }

    private function shopDocumentExpiryFilterKey(string $type, mixed $expiredAtRaw): string
    {
        if ($type !== 'business' || empty($expiredAtRaw)) {
            return 'none';
        }

        $today = Carbon::today();
        $expiredAt = Carbon::parse((string) $expiredAtRaw)->startOfDay();

        if ($expiredAt->lt($today)) {
            return 'expired';
        }
        if ($expiredAt->lte($today->copy()->addMonthsNoOverflow(3))) {
            return 'within_3_months';
        }

        return 'valid';
    }

    private function castTypeLabel(string $type): string
    {
        return match ($type) {
            'passport' => '旅券',
            'my_number' => 'マイナンバーカード',
            default => '運転免許証',
        };
    }

    private function shopTypeLabel(string $type): string
    {
        return match ($type) {
            'entertainment' => '風営許可証',
            default => '営業許可証',
        };
    }

    private function statusLabel(int $status): string
    {
        return match ($status) {
            0 => 'アップロード済み',
            2 => '承認済み',
            3 => '不備・却下',
            default => '未承認',
        };
    }

    private function statusKey(int $status): string
    {
        return match ($status) {
            0 => 'draft',
            2 => 'approved',
            3 => 'rejected',
            default => 'pending',
        };
    }

    private function statusSortRank(int $status): int
    {
        return match ($status) {
            0 => 0,
            1 => 0,
            3 => 1,
            2 => 2,
            default => 9,
        };
    }

    /**
     * DB に保存されている image_path を、public ディスク上の相対パスに正規化する。
     */
    public function shopLicenseRelativePublicPath(?string $storedPath): ?string
    {
        if (empty($storedPath)) {
            return null;
        }

        $path = str_replace('\\', '/', $storedPath);
        if (str_starts_with($path, 'public/')) {
            return substr($path, strlen('public/'));
        }

        return ltrim($path, '/');
    }

    private function documentUrl(?string $path): ?string
    {
        $relative = $this->shopLicenseRelativePublicPath($path);
        if ($relative === null) {
            return null;
        }

        return Storage::disk('public')->url($relative);
    }

    private function formatDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return date('Y-m-d H:i', strtotime((string) $value));
    }
}
