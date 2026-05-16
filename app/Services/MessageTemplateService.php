<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageTemplateService
{
    /**
     * @return array<int, array{key:string,title:string,body:string}>
     */
    public function getTemplates(string $group): array
    {
        if (Schema::hasTable('message_templates')) {
            $hasCategory = Schema::hasColumn('message_templates', 'category');
            $columns = ['template_key', 'title', 'body'];
            if ($hasCategory) {
                $columns[] = 'category';
            }
            $rows = DB::table('message_templates')
                ->where('template_group', $group)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get($columns);

            if ($rows->isNotEmpty()) {
                return $rows->map(fn ($row) => [
                    'key' => (string) $row->template_key,
                    'title' => (string) $row->title,
                    'body' => (string) $row->body,
                    'category' => $hasCategory ? (string) ($row->category ?? '') : '',
                ])->all();
            }
        }

        return $this->defaultTemplates()[$group] ?? [];
    }

    /**
     * @param array<int, string> $groups
     * @return array<string, array<int, array{key:string,title:string,body:string}>>
     */
    public function getGroupedTemplates(array $groups): array
    {
        $templates = [];
        foreach ($groups as $group) {
            $templates[$group] = $this->getTemplates($group);
        }

        return $templates;
    }

    public function getDefaultBody(string $group): string
    {
        return $this->getTemplates($group)[0]['body'] ?? '';
    }

    /**
     * クイック定型文の最大スロット数（1〜4）。
     */
    public const QUICK_TEMPLATE_SLOTS = 4;

    /**
     * ユーザ（キャスト／店舗）の 4 スロット分の定型文を返す。
     * カスタムが無いスロットはデフォルト文を使う。
     *
     * @return array<int, array{slot:int,title:string,body:string,is_custom:bool,default_title:string,default_body:string}>
     */
    public function getQuickTemplateSlots(string $ownerType, ?string $ownerId): array
    {
        $defaults = $this->getDefaultQuickTemplates($ownerType);
        $customs = [];

        if ($ownerId && Schema::hasTable('user_talk_templates')) {
            $rows = DB::table('user_talk_templates')
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('is_active', true)
                ->whereBetween('sort_order', [1, self::QUICK_TEMPLATE_SLOTS])
                ->get(['id', 'sort_order', 'title', 'body']);
            foreach ($rows as $row) {
                $customs[(int) $row->sort_order] = [
                    'title' => (string) $row->title,
                    'body' => (string) $row->body,
                ];
            }
        }

        $slots = [];
        for ($slot = 1; $slot <= self::QUICK_TEMPLATE_SLOTS; $slot++) {
            $default = $defaults[$slot - 1] ?? null;
            $defaultTitle = (string) ($default['title'] ?? '定型文' . $slot);
            $defaultBody = (string) ($default['body'] ?? '');
            $custom = $customs[$slot] ?? null;
            $slots[] = [
                'slot' => $slot,
                'is_custom' => $custom !== null,
                'title' => $custom['title'] ?? $defaultTitle,
                'body' => $custom['body'] ?? $defaultBody,
                'default_title' => $defaultTitle,
                'default_body' => $defaultBody,
            ];
        }

        return $slots;
    }

    /**
     * 既定（プリセット）のクイック定型文を返す。スロットのフォールバックに使う。
     *
     * @return array<int, array{key:string,title:string,body:string,category:string}>
     */
    public function getDefaultQuickTemplates(string $ownerType): array
    {
        return $this->getTemplates($ownerType === 'cast' ? 'talk_quick_cast' : 'talk_quick_shop');
    }

    /**
     * @return array<string, array<int, array{key:string,title:string,body:string}>>
     */
    private function defaultTemplates(): array
    {
        return [
            'document_reject_cast' => [
                [
                    'key' => 'image_blur',
                    'title' => '画像不鮮明',
                    'body' => '本人確認書類の画像が不鮮明で内容を確認できませんでした。文字がはっきり読める状態で再撮影のうえ、再提出をお願いします。',
                ],
                [
                    'key' => 'missing_back',
                    'title' => '裏面不足',
                    'body' => '本人確認書類の確認に必要な裏面画像が不足しています。裏面もあわせて再提出をお願いします。',
                ],
                [
                    'key' => 'expired',
                    'title' => '有効期限切れ',
                    'body' => '提出いただいた本人確認書類は有効期限切れのため承認できません。有効期限内の書類をご提出ください。',
                ],
            ],
            'document_reject_shop' => [
                [
                    'key' => 'image_blur',
                    'title' => '画像不鮮明',
                    'body' => '提出書類の画像が不鮮明で内容を確認できませんでした。書類全体が鮮明に写る状態で再提出をお願いします。',
                ],
                [
                    'key' => 'page_missing',
                    'title' => '必要ページ不足',
                    'body' => '許可内容の確認に必要なページが不足しています。必要事項が確認できるページを含めて再提出をお願いします。',
                ],
                [
                    'key' => 'expired',
                    'title' => '有効期限切れ',
                    'body' => '提出いただいた書類は有効期限切れ、または有効性を確認できませんでした。最新の有効な書類をご提出ください。',
                ],
            ],
            'talk_hired' => [
                [
                    'key' => 'standard',
                    'title' => '標準',
                    'body' => 'この度は面談ありがとうございました。ぜひ採用で進めさせていただきたいと考えております。今後の流れについて、あらためてご連絡いたします。',
                ],
                [
                    'key' => 'welcome',
                    'title' => '歓迎強め',
                    'body' => '本日はありがとうございました。ぜひご一緒したいと考えておりますので、採用でご案内いたします。勤務開始時期や手続きの詳細を追ってお送りします。',
                ],
                [
                    'key' => 'short',
                    'title' => '簡潔',
                    'body' => '面談ありがとうございました。選考の結果、採用でご案内いたします。詳細は追ってご連絡します。',
                ],
            ],
            'talk_rejected' => [
                [
                    'key' => 'standard',
                    'title' => '標準',
                    'body' => 'この度はご応募ありがとうございました。慎重に検討させていただいた結果、今回は見送らせていただくこととなりました。またご縁がございましたらよろしくお願いいたします。',
                ],
                [
                    'key' => 'polite',
                    'title' => '丁寧',
                    'body' => 'この度はお時間をいただきありがとうございました。誠に恐縮ですが、今回の選考では不採用とさせていただきます。今後のご活躍を心よりお祈りしております。',
                ],
                [
                    'key' => 'future',
                    'title' => '再応募歓迎',
                    'body' => 'ご応募ありがとうございました。今回は見送らせていただく結果となりましたが、募集状況が変わりましたら再度ご相談させていただく場合がございます。その際はよろしくお願いいたします。',
                ],
            ],
            // キャスト→店舗 のクイックテンプレ
            'talk_quick_cast' => [
                ['key' => 'cast_greet_initial',  'category' => '初回挨拶', 'title' => 'はじめまして',     'body' => 'はじめまして。求人を拝見してご連絡いたしました。詳細についてお伺いできますと幸いです。よろしくお願いいたします。'],
                ['key' => 'cast_greet_thanks',   'category' => '初回挨拶', 'title' => 'ご返信ありがとうございます', 'body' => 'ご返信ありがとうございます。前向きに検討させていただきたいと思いますので、どうぞよろしくお願いいたします。'],
                ['key' => 'cast_ask_shift',     'category' => '確認・質問', 'title' => 'シフトについて', 'body' => '出勤可能なシフトや最低出勤本数について、もう少し詳しく教えていただけますでしょうか。'],
                ['key' => 'cast_ask_pay',       'category' => '確認・質問', 'title' => '報酬について',   'body' => '体験時の時給や本入店後のバック率について、念のため確認させてください。'],
                ['key' => 'cast_ask_dress',     'category' => '確認・質問', 'title' => '服装・身だしなみ', 'body' => '当日の服装や髪色などのドレスコードがあれば事前に教えていただけますでしょうか。'],
                ['key' => 'cast_interview_ok', 'category' => '面談調整', 'title' => '面談OK',         'body' => '面談の件、承知しました。ご提示いただいた日程で問題ございません。当日はよろしくお願いいたします。'],
                ['key' => 'cast_interview_change', 'category' => '面談調整', 'title' => '日程変更のお願い', 'body' => '申し訳ございません、ご提示いただいた日時に予定が入ってしまいました。別の候補日をご相談させていただけますでしょうか。'],
                ['key' => 'cast_late',          'category' => '面談調整', 'title' => '遅刻のご連絡', 'body' => '大変申し訳ございません、電車の遅延により〇分ほど遅れて到着しそうです。ご迷惑をおかけし恐れ入ります。'],
                ['key' => 'cast_thanks_after',  'category' => 'お礼・締め', 'title' => '面談後のお礼',  'body' => '本日はお時間をいただきありがとうございました。前向きに検討のうえ、改めてご連絡させていただきます。'],
                ['key' => 'cast_decline',       'category' => 'お礼・締め', 'title' => '辞退のご連絡',  'body' => 'ご検討いただきありがとうございました。誠に勝手ながら、今回はご縁を見送らせていただければと思います。よろしくお願いいたします。'],
            ],
            // 店舗→キャスト のクイックテンプレ
            'talk_quick_shop' => [
                ['key' => 'shop_greet',         'category' => '初回挨拶', 'title' => 'ご連絡ありがとうございます', 'body' => 'ご連絡ありがとうございます。当店にご興味を持っていただき大変嬉しく思います。お気軽にご質問くださいませ。'],
                ['key' => 'shop_intro',         'category' => '初回挨拶', 'title' => 'お店のご案内', 'body' => '当店は〇〇エリアで営業しているお店です。落ち着いた雰囲気で、初心者の方も安心して働ける環境を整えております。'],
                ['key' => 'shop_ask_when',     'category' => '確認・質問', 'title' => '勤務開始希望時期', 'body' => 'ご質問ありがとうございます。差し支えなければ、勤務開始のご希望時期や週の出勤可能日数を教えていただけますか。'],
                ['key' => 'shop_ask_exp',      'category' => '確認・質問', 'title' => 'ご経験の確認',  'body' => '差し支えなければ、これまでのご経験や現在の在籍状況について教えていただけますでしょうか。'],
                ['key' => 'shop_offer_interview', 'category' => '面談調整', 'title' => '面談のご提案', 'body' => 'ぜひ一度面談をさせていただければと思います。ご都合のよい日時をいくつか教えていただけますでしょうか。'],
                ['key' => 'shop_interview_confirm', 'category' => '面談調整', 'title' => '面談確定', 'body' => 'ご返信ありがとうございます。それでは下記日時にて面談をさせていただきます。当日お会いできるのを楽しみにしております。'],
                ['key' => 'shop_access',       'category' => '面談調整', 'title' => 'アクセス案内', 'body' => '店舗のアクセス情報をお送りします。最寄り駅からの道順でご不明な点があればお気軽にご連絡ください。'],
                ['key' => 'shop_thanks_after',  'category' => 'お礼・締め', 'title' => '面談後のお礼', 'body' => '本日はお越しいただきありがとうございました。社内で検討のうえ、改めて結果をご連絡させていただきます。'],
                ['key' => 'shop_welcome',       'category' => 'お礼・締め', 'title' => '入店歓迎',     'body' => 'この度はご縁をいただきありがとうございます。スタッフ一同、心より歓迎いたします。これからどうぞよろしくお願いいたします。'],
            ],
        ];
    }
}
