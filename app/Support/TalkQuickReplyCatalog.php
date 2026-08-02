<?php

namespace App\Support;

/**
 * トークのクイック定型文カタログ。
 *
 * 以前は TalkController::buildQuickReplySuggestions() / buildAllQuickReplySuggestionsByStatus()
 * 内に ~80 行のハードコード配列があった。役割 × ステータスの組み合わせが増えるたびに
 * controller が肥大化していたため、Support クラスとして切り出した (2026-08-02)。
 *
 * ステータスコードは TalkController のプライベート定数と同じ値を維持する。
 * 双方で使いたくなったら App\Enums などに更に切り出せばよいが、今は変更点を最小にする方針。
 *
 * 将来 DB 化する場合は forStatus() / allByStatus() のシグネチャを保ちながら
 * 実装だけ差し替えれば controller 側は無変更で済む。
 */
final class TalkQuickReplyCatalog
{
    public const STATUS_CHATTING          = 1;
    public const STATUS_INTERVIEW_PENDING = 2;
    public const STATUS_INTERVIEW_FIXED   = 3;
    public const STATUS_HIRED             = 4;
    public const STATUS_REJECTED          = 5;
    public const STATUS_HIRED_FULLTIME    = 6;
    public const STATUS_REJECTED_TRIAL    = 7;

    /**
     * 指定ステータス × 役割の定型文候補を返す。
     *
     * @return array<int, array{category:string, body:string}>
     */
    public function forStatus(bool $isCastPortal, int $status): array
    {
        return $isCastPortal
            ? $this->castToShop($status)
            : $this->shopToCast($status);
    }

    /**
     * 定型文編集画面などで「すべての状況の候補文」を並べて返す。
     *
     * @return array<int, array{status_code:int, status_key:string, status_label:string, items: array<int, array{category:string, body:string}>}>
     */
    public function allByStatus(bool $isCastPortal): array
    {
        $groups = [
            ['code' => self::STATUS_CHATTING,          'label' => 'やり取り中（初回・雑談）'],
            ['code' => self::STATUS_INTERVIEW_PENDING, 'label' => '面談日調整中'],
            ['code' => self::STATUS_INTERVIEW_FIXED,   'label' => '面談日確定済み'],
            ['code' => self::STATUS_HIRED,             'label' => '採用'],
            ['code' => self::STATUS_REJECTED,          'label' => '不採用・お断り'],
        ];

        return array_values(array_filter(array_map(function (array $g) use ($isCastPortal) {
            $items = $this->forStatus($isCastPortal, $g['code']);
            if (empty($items)) {
                return null;
            }
            return [
                'status_code'  => $g['code'],
                'status_key'   => self::statusKey($g['code']),
                'status_label' => $g['label'],
                'items'        => $items,
            ];
        }, $groups)));
    }

    /**
     * 内部ステータスコード → フロント側で照合する文字列キーへの変換。
     * TalkController::applicationStatusCode() と同じマッピングを提供する。
     */
    public static function statusKey(int $status): string
    {
        return match ($status) {
            self::STATUS_INTERVIEW_PENDING => 'interview_pending',
            self::STATUS_INTERVIEW_FIXED   => 'interview_fixed',
            self::STATUS_HIRED,
            self::STATUS_HIRED_FULLTIME    => 'hired',
            self::STATUS_REJECTED,
            self::STATUS_REJECTED_TRIAL    => 'rejected',
            default                        => 'chatting',
        };
    }

    /**
     * キャスト → 店舗の定型文候補。
     *
     * @return array<int, array{category:string, body:string}>
     */
    private function castToShop(int $status): array
    {
        return match ($status) {
            self::STATUS_INTERVIEW_PENDING => [
                ['category' => 'thanks',   'body' => '面談候補日ありがとうございます！確認して回答しますね。'],
                ['category' => 'schedule', 'body' => '第一希望の日程で調整をお願いします。'],
                ['category' => 'schedule', 'body' => 'すみません、その日程は難しいので別日を提案いただけますか？'],
                ['category' => 'question', 'body' => '面談はどれくらい時間がかかりますか？'],
                ['category' => 'question', 'body' => 'オンラインでの面談も可能でしょうか？'],
            ],
            self::STATUS_INTERVIEW_FIXED => [
                ['category' => 'thanks',   'body' => '面談当日はよろしくお願いします！'],
                ['category' => 'question', 'body' => '当日の持ち物や服装の指定はありますか？'],
                ['category' => 'question', 'body' => '場所の詳しいアクセスを教えていただけますか？'],
                ['category' => 'question', 'body' => '当日はどなたをお訪ねすればよいですか？'],
                ['category' => 'status',   'body' => 'すみません、少し遅れそうです。到着次第ご連絡します。'],
                ['category' => 'status',   'body' => '到着しました。入口はどちらでしょうか？'],
            ],
            self::STATUS_HIRED,
            self::STATUS_HIRED_FULLTIME => [
                ['category' => 'thanks',   'body' => '採用ありがとうございます！精一杯頑張ります。'],
                ['category' => 'schedule', 'body' => '初出勤日はいつを予定していますか？'],
                ['category' => 'question', 'body' => '当日の持ち物・服装を教えてください。'],
                ['category' => 'question', 'body' => '出勤時間の何分前に伺えばよいですか？'],
                ['category' => 'question', 'body' => '入店時の手続きで必要な書類はありますか？'],
            ],
            self::STATUS_REJECTED,
            self::STATUS_REJECTED_TRIAL => [
                ['category' => 'thanks', 'body' => 'ご連絡ありがとうございました。'],
                ['category' => 'thanks', 'body' => 'またご縁がありましたらぜひよろしくお願いします。'],
            ],
            default => [
                // やり取り中（応募直後・雑談期）
                ['category' => 'intro',    'body' => 'はじめまして！求人を拝見してご連絡しました。'],
                ['category' => 'intro',    'body' => 'プロフィールをご覧いただきありがとうございます。ぜひお話しできればと思います。'],
                ['category' => 'schedule', 'body' => '体入を希望しています。空いている日程はありますか？'],
                ['category' => 'question', 'body' => 'お店の雰囲気について教えてください。'],
                ['category' => 'question', 'body' => '時給や採用条件について詳しく知りたいです。'],
                ['category' => 'question', 'body' => '未経験でも安心して働けますか？'],
            ],
        };
    }

    /**
     * 店舗 → キャストの定型文候補。
     *
     * @return array<int, array{category:string, body:string}>
     */
    private function shopToCast(int $status): array
    {
        return match ($status) {
            self::STATUS_INTERVIEW_PENDING => [
                ['category' => 'schedule', 'body' => '面談の候補日をお送りしました。ご都合はいかがでしょうか？'],
                ['category' => 'schedule', 'body' => 'ご都合の良い日程があればお気軽に教えてください。'],
                ['category' => 'schedule', 'body' => '日程が合わない場合は別日を提案いたします。'],
                ['category' => 'question', 'body' => '面談は対面／オンラインどちらをご希望ですか？'],
                // 面談前でも当日ヘルプの相談は現実的にあり得るため定型を用意
                ['category' => 'help',     'body' => '今からヘルプで入れませんか？急遽ピンチヒッターを探しています。'],
            ],
            self::STATUS_INTERVIEW_FIXED => [
                ['category' => 'status',   'body' => '面談当日、お待ちしております！'],
                ['category' => 'status',   'body' => 'お気をつけてお越しください。'],
                ['category' => 'status',   'body' => '当日は私服でお越しいただいて大丈夫です。'],
                ['category' => 'status',   'body' => '到着されましたらこのトークでお知らせください。'],
                ['category' => 'question', 'body' => '当日は身分証と印鑑をお持ちください。'],
            ],
            self::STATUS_HIRED,
            self::STATUS_HIRED_FULLTIME => [
                ['category' => 'thanks',   'body' => 'この度は採用となりました！おめでとうございます。'],
                ['category' => 'schedule', 'body' => '初出勤日について改めてご案内します。'],
                ['category' => 'question', 'body' => '当日の集合時間・持ち物のご案内です。'],
                ['category' => 'status',   'body' => '不明点があればいつでもご連絡ください。'],
            ],
            self::STATUS_REJECTED,
            self::STATUS_REJECTED_TRIAL => [
                ['category' => 'thanks', 'body' => 'この度はご応募ありがとうございました。'],
                ['category' => 'thanks', 'body' => 'またのご縁がありましたらよろしくお願いいたします。'],
            ],
            default => [
                // 「今すぐ入れる」宣言中／オンライン中キャストへの即時ヘルプ依頼テンプレ。
                // 店舗ホームで Tier A/B チップを見た店舗が最短でタップして送れるよう先頭配置
                ['category' => 'help',     'body' => '今からヘルプで入れませんか？急遽ピンチヒッターを探しています。'],
                ['category' => 'help',     'body' => '本日◯時から◯時までヘルプ入れる方いませんか？ご対応可能なら折り返しお願いします！'],
                ['category' => 'intro',    'body' => 'はじめまして！ご興味をお持ちいただきありがとうございます。'],
                ['category' => 'intro',    'body' => 'プロフィール拝見しました。ぜひ一度お話しできれば嬉しいです。'],
                ['category' => 'schedule', 'body' => '一度お店の雰囲気を見にいらしてください。'],
                ['category' => 'schedule', 'body' => '面談のご都合はいかがでしょうか？'],
                ['category' => 'question', 'body' => 'ご質問があればお気軽にどうぞ！'],
                ['category' => 'question', 'body' => '希望の勤務日数や時間帯があれば教えてください。'],
            ],
        };
    }
}
