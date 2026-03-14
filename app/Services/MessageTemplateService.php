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
            $rows = DB::table('message_templates')
                ->where('template_group', $group)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['template_key', 'title', 'body']);

            if ($rows->isNotEmpty()) {
                return $rows->map(fn ($row) => [
                    'key' => (string) $row->template_key,
                    'title' => (string) $row->title,
                    'body' => (string) $row->body,
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
        ];
    }
}
