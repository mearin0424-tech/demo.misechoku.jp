<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceTemplateSettingsService
{
    public const KEY_ISSUER_NAME = 'issuer_name';
    public const KEY_ISSUER_EMAIL = 'issuer_email';
    public const KEY_LOGO_URL = 'logo_url';
    public const KEY_FOOTER_TEXT = 'footer_text';

    private const DEFAULTS = [
        self::KEY_ISSUER_NAME => 'ミセチョク運営事務局',
        self::KEY_ISSUER_EMAIL => 'support@misechoku.jp',
        self::KEY_LOGO_URL => '',
        self::KEY_FOOTER_TEXT => '',
    ];

    public function get(string $key): string
    {
        if (!array_key_exists($key, self::DEFAULTS)) {
            return '';
        }
        if (!Schema::hasTable('invoice_template_settings')) {
            return (string) (self::DEFAULTS[$key] ?? '');
        }
        $row = DB::table('invoice_template_settings')->where('key', $key)->first();
        return $row && $row->value !== null ? (string) $row->value : (string) (self::DEFAULTS[$key] ?? '');
    }

    public function set(string $key, ?string $value): void
    {
        if (!Schema::hasTable('invoice_template_settings')) {
            return;
        }
        $value = $value !== null ? trim($value) : null;
        DB::table('invoice_template_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }

    /**
     * 請求書ビュー用の設定配列（issuer_name, issuer_email, logo_url, footer_text）
     */
    public function getForInvoice(): array
    {
        return [
            'issuer_name' => $this->get(self::KEY_ISSUER_NAME),
            'issuer_email' => $this->get(self::KEY_ISSUER_EMAIL),
            'logo_url' => $this->get(self::KEY_LOGO_URL),
            'footer_text' => $this->get(self::KEY_FOOTER_TEXT),
        ];
    }

    public function saveFromRequest(array $data): void
    {
        foreach (array_keys(self::DEFAULTS) as $key) {
            if (array_key_exists($key, $data)) {
                $this->set($key, $data[$key] === null ? null : (string) $data[$key]);
            }
        }
    }
}
