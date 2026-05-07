<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastIdentityDocument extends Model
{
    public const STATUS_PENDING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;

    /** 書類カテゴリ */
    public const CATEGORY_PHOTO_ID = 'photo_id';        // 顔写真付身分証（運転免許・パスポート等）1枚で完結
    public const CATEGORY_NON_PHOTO_ID = 'non_photo_id'; // 顔写真なし身分証（保険証等）。CATEGORY_ADDRESS_PROOF とセットで提出
    public const CATEGORY_ADDRESS_PROOF = 'address_proof'; // 住所確認書類（住民票・公共料金等）

    /** カテゴリごとの type 候補（バリデーション・UI用） */
    public const TYPES_PHOTO_ID = ['driver_license', 'passport', 'mynumber_card', 'residence_card'];
    public const TYPES_NON_PHOTO_ID = ['health_insurance', 'pension_book'];
    public const TYPES_ADDRESS_PROOF = ['residence_certificate', 'utility_bill'];

    protected $table = 'cast_identity_documents';

    protected $fillable = [
        'cast_id',
        'category',
        'type',
        'image_path_front',
        'image_path_back',
        'status',
        'ng_reason',
        'expired_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
            'approved_at' => 'datetime',
            // 機密ファイルパス＋差戻し理由を暗号化（復号失敗時は null）
            'image_path_front' => \App\Casts\SafeEncrypted::class,
            'image_path_back'  => \App\Casts\SafeEncrypted::class,
            'ng_reason'        => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }

    /**
     * 指定カテゴリに有効な type 一覧を返す。
     */
    public static function allowedTypesFor(string $category): array
    {
        return match ($category) {
            self::CATEGORY_PHOTO_ID => self::TYPES_PHOTO_ID,
            self::CATEGORY_NON_PHOTO_ID => self::TYPES_NON_PHOTO_ID,
            self::CATEGORY_ADDRESS_PROOF => self::TYPES_ADDRESS_PROOF,
            default => [],
        };
    }

    /**
     * type 値からカテゴリを逆引き（旧データ互換含む）。
     */
    public static function categoryForType(string $type): string
    {
        if (in_array($type, self::TYPES_PHOTO_ID, true)) return self::CATEGORY_PHOTO_ID;
        if (in_array($type, self::TYPES_NON_PHOTO_ID, true)) return self::CATEGORY_NON_PHOTO_ID;
        if (in_array($type, self::TYPES_ADDRESS_PROOF, true)) return self::CATEGORY_ADDRESS_PROOF;
        return self::CATEGORY_PHOTO_ID; // 旧データ（id_card 等）は顔写真付き扱い
    }

    /**
     * キャストが本人確認完了とみなせるかを判定する。
     * - 顔写真付身分証 1枚（承認済）
     * - もしくは 顔写真なし身分証（承認済）＋ 住所確認書類（承認済）の両方
     */
    public static function isCastVerified(string $castId): bool
    {
        $approved = self::query()
            ->where('cast_id', $castId)
            ->where('status', self::STATUS_APPROVED)
            ->pluck('category')
            ->all();

        if (in_array(self::CATEGORY_PHOTO_ID, $approved, true)) {
            return true;
        }
        return in_array(self::CATEGORY_NON_PHOTO_ID, $approved, true)
            && in_array(self::CATEGORY_ADDRESS_PROOF, $approved, true);
    }
}
