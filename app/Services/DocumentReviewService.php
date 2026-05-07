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
        $byCategory = $documents->keyBy('category');

        $photoId = $byCategory->get(CastIdentityDocument::CATEGORY_PHOTO_ID);
        $nonPhotoId = $byCategory->get(CastIdentityDocument::CATEGORY_NON_PHOTO_ID);
        $addressProof = $byCategory->get(CastIdentityDocument::CATEGORY_ADDRESS_PROOF);

        // 提出パターンの推定（既に提出されている書類から）：
        //   - photo_id が1つでもあれば 'photo'
        //   - non_photo_id か address_proof が1つでもあれば 'non_photo'
        //   - どちらもなければ 'photo'（既定）
        $detectedPattern = $photoId
            ? 'photo'
            : (($nonPhotoId || $addressProof) ? 'non_photo' : 'photo');

        return [
            'status' => $this->castStatusKey($documents),
            'is_verified' => CastIdentityDocument::isCastVerified($castId),
            'documents' => $documents
                ->map(fn (CastIdentityDocument $document) => $this->mapCastDocument($document))
                ->all(),
            'latest_document' => $latestDocument ? $this->mapCastDocument($latestDocument) : null,
            'pattern' => $detectedPattern, // 'photo' | 'non_photo'
            'category_documents' => [
                'photo_id' => $photoId ? $this->mapCastDocument($photoId) : null,
                'non_photo_id' => $nonPhotoId ? $this->mapCastDocument($nonPhotoId) : null,
                'address_proof' => $addressProof ? $this->mapCastDocument($addressProof) : null,
            ],
            'allowed_types' => [
                'photo_id' => CastIdentityDocument::TYPES_PHOTO_ID,
                'non_photo_id' => CastIdentityDocument::TYPES_NON_PHOTO_ID,
                'address_proof' => CastIdentityDocument::TYPES_ADDRESS_PROOF,
            ],
            'type_labels' => [
                'driver_license' => $this->castTypeLabel('driver_license'),
                'passport' => $this->castTypeLabel('passport'),
                'mynumber_card' => $this->castTypeLabel('mynumber_card'),
                'residence_card' => $this->castTypeLabel('residence_card'),
                'health_insurance' => $this->castTypeLabel('health_insurance'),
                'pension_book' => $this->castTypeLabel('pension_book'),
                'residence_certificate' => $this->castTypeLabel('residence_certificate'),
                'utility_bill' => $this->castTypeLabel('utility_bill'),
            ],
        ];
    }

    public function uploadCastIdentityDocument(
        string $castId,
        string $type,
        UploadedFile $frontFile,
        ?UploadedFile $backFile = null,
        ?string $expiredAt = null,
        ?string $category = null
    ): CastIdentityDocument {
        // category が未指定の場合は type から推定（旧呼び出し互換）
        $category = $category ?: CastIdentityDocument::categoryForType($type);

        // 機密ファイルは Web から直接アクセスできない private ディスク (storage/app/private) に保存する。
        // 配信は必ず認証済みコントローラ経由で行う。
        $frontPath = 'private/' . $frontFile->store('casts/identity', 'private');
        $backPath = $backFile ? 'private/' . $backFile->store('casts/identity', 'private') : null;

        // 1キャストあたり同一カテゴリは1書類のみ。type を変更した場合は同じ category 行を上書きする。
        $document = CastIdentityDocument::query()->updateOrCreate(
            [
                'cast_id' => $castId,
                'category' => $category,
            ],
            [
                'type' => $type,
                'image_path_front' => $frontPath,
                'image_path_back' => $backPath,
                'status' => CastIdentityDocument::STATUS_PENDING,
                'ng_reason' => null,
                'expired_at' => $expiredAt ?: null,
                'approved_at' => null,
            ]
        );

        // パターンA（顔写真付）に切り替えた場合は、Bで残っていた non_photo_id / address_proof は不要になるため削除
        if ($category === CastIdentityDocument::CATEGORY_PHOTO_ID) {
            CastIdentityDocument::query()
                ->where('cast_id', $castId)
                ->whereIn('category', [CastIdentityDocument::CATEGORY_NON_PHOTO_ID, CastIdentityDocument::CATEGORY_ADDRESS_PROOF])
                ->delete();
        }

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
        // 機密ファイルは Web から直接アクセスできない private ディスク (storage/app/private) に保存する。
        // 配信は必ず認証済みコントローラ経由で行う。
        $path = 'private/' . $file->store('shops/documents', 'private');

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

        // 「本人確認完了」の判定はカテゴリ単位で行う：
        //   - photo_id 1枚が承認済み、または
        //   - non_photo_id ＋ address_proof の両方が承認済み
        $approvedCategories = $documents
            ->filter(fn (CastIdentityDocument $d) => $d->status === CastIdentityDocument::STATUS_APPROVED)
            ->pluck('category')
            ->unique();

        $isApproved = $approvedCategories->contains(CastIdentityDocument::CATEGORY_PHOTO_ID)
            || ($approvedCategories->contains(CastIdentityDocument::CATEGORY_NON_PHOTO_ID)
                && $approvedCategories->contains(CastIdentityDocument::CATEGORY_ADDRESS_PROOF));

        if ($isApproved) {
            return 'approved';
        }

        if ($documents->contains(fn (CastIdentityDocument $d) => $d->status === CastIdentityDocument::STATUS_PENDING)) {
            return 'pending';
        }

        if ($documents->contains(fn (CastIdentityDocument $d) => $d->status === CastIdentityDocument::STATUS_REJECTED)) {
            return 'rejected';
        }

        // 全部承認済みだがパターン未充足（B のうち片方だけ承認）→ pending 扱い
        return 'pending';
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
        $category = (string) ($document->category ?? CastIdentityDocument::categoryForType((string) $document->type));
        return [
            'id' => $document->id,
            'category' => $category,
            'category_label' => $this->castCategoryLabel($category),
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
            'front_url' => $this->castIdentityAdminFileUrl($document, 'front'),
            'back_url' => $this->castIdentityAdminFileUrl($document, 'back'),
        ];
    }

    public function castCategoryLabel(string $category): string
    {
        return match ($category) {
            CastIdentityDocument::CATEGORY_PHOTO_ID => '顔写真付身分証',
            CastIdentityDocument::CATEGORY_NON_PHOTO_ID => '顔写真なし身分証',
            CastIdentityDocument::CATEGORY_ADDRESS_PROOF => '住所確認書類',
            default => '—',
        };
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
            'front_url' => $this->castIdentityAdminFileUrl($document, 'front'),
            'back_url' => $this->castIdentityAdminFileUrl($document, 'back'),
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
            'file_url' => $this->shopLicenseAdminFileUrl($document),
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
            'driver_license' => '運転免許証',
            'passport' => '旅券（パスポート）',
            'mynumber_card', 'my_number' => 'マイナンバーカード',
            'residence_card' => '在留カード',
            'health_insurance' => '健康保険証',
            'pension_book' => '年金手帳',
            'residence_certificate' => '住民票',
            'utility_bill' => '公共料金領収書',
            'id_card' => '身分証（旧）',
            default => $type ?: '—',
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
     * 旧データ（public/...）と新データ（private/...）の両方に対応するため、
     * 公開URL生成は新形式では null（=コントローラ経由配信のみ）。
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
        if (str_starts_with($path, 'private/')) {
            // 新形式は Web 公開しないため null を返す（呼び出し側はコントローラ経由でアクセスする）
            return null;
        }

        return ltrim($path, '/');
    }

    /**
     * DB に保存されている image_path から、ディスク名と相対パスを返す。
     *
     * - 'private/...' → 'local' ディスク, パスは 'private/...' のまま（root=storage_path('app')）
     * - 'public/...'  → 'local' ディスク, パスは 'public/...' のまま（旧データ互換）
     * - その他       → 'public' ディスク（旧データ互換）
     *
     * @return array{0:string, 1:string}|null  [disk, relativePath] または null（パス無し）
     */
    public function resolveDocumentDiskPath(?string $storedPath): ?array
    {
        if (empty($storedPath)) {
            return null;
        }
        $path = str_replace('\\', '/', $storedPath);
        if (str_starts_with($path, 'private/') || str_starts_with($path, 'public/')) {
            return ['local', $path];
        }
        return ['public', ltrim($path, '/')];
    }

    /**
     * ファイルが新形式（private ディスク）に保存されているかを返す。
     */
    public function isPrivateStored(?string $storedPath): bool
    {
        if (empty($storedPath)) {
            return false;
        }
        return str_starts_with(str_replace('\\', '/', $storedPath), 'private/');
    }

    private function documentUrl(?string $path): ?string
    {
        $relative = $this->shopLicenseRelativePublicPath($path);
        if ($relative === null) {
            // 新形式（private）はコントローラ経由のルート URL を使う必要があるため、
            // 表示側で route('shop.mypage.documents.view', [...]) などに切り替える。
            return null;
        }

        return Storage::disk('public')->url($relative);
    }

    /**
     * 管理画面（運営）からキャスト本人確認書類を閲覧するためのファイル URL。
     * - 旧形式（public/...）：そのまま公開URL
     * - 新形式（private/...）：認証＋権限付き管理画面ルート
     *
     * @param  object  $document  CastIdentityDocument or DB row stdClass
     */
    public function castIdentityAdminFileUrl(object $document, string $side): ?string
    {
        $path = $side === 'back' ? ($document->image_path_back ?? null) : ($document->image_path_front ?? null);
        if (empty($path)) {
            return null;
        }
        if ($this->isPrivateStored($path)) {
            return route('admin.verification.cast.file', ['document' => $document->id, 'side' => $side]);
        }
        return $this->documentUrl($path);
    }

    /**
     * 管理画面（運営）から店舗書類を閲覧するためのファイル URL。
     *
     * @param  object  $document  ShopLicenseDocument or DB row stdClass
     */
    public function shopLicenseAdminFileUrl(object $document): ?string
    {
        $path = $document->image_path ?? null;
        if (empty($path)) {
            return null;
        }
        if ($this->isPrivateStored($path)) {
            return route('admin.verification.shopdoc.file', ['document' => $document->id]);
        }
        return $this->documentUrl($path);
    }

    private function formatDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return date('Y-m-d H:i', strtotime((string) $value));
    }
}
