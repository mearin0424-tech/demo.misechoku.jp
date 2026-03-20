<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'push:vapid';
    protected $description = 'Generate VAPID keys for Web Push and output .env lines';

    public function handle(): int
    {
        if (!\function_exists('openssl_pkey_new') || !\function_exists('openssl_pkey_get_details')) {
            $this->error('OpenSSL 拡張が無効です。php.ini で extension=openssl を有効化してください。');

            return 1;
        }

        $ecKeyType = defined('OPENSSL_KEYTYPE_EC') ? \OPENSSL_KEYTYPE_EC : 3;
        $config = [
            'private_key_type' => $ecKeyType,
            'curve_name' => 'prime256v1',
        ];
        $res = \openssl_pkey_new($config);
        if ($res === false) {
            $this->error('OpenSSL key generation failed. EC(P-256) に対応した OpenSSL 拡張が必要です。');
            return 1;
        }
        $details = \openssl_pkey_get_details($res);
        if (!$details || !isset($details['ec']['x'], $details['ec']['y'], $details['ec']['d'])) {
            $this->error('Could not get EC key details.');
            return 1;
        }

        $publicKeyBin = "\x04" . $details['ec']['x'] . $details['ec']['y'];
        $publicKey = $this->base64UrlEncode($publicKeyBin);
        $privateKey = $this->base64UrlEncode($details['ec']['d']);

        $this->line('');
        $this->line('Add these to your .env file:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $publicKey);
        $this->line('VAPID_PRIVATE_KEY=' . $privateKey);
        $this->line('VAPID_SUBJECT=mailto:your-email@example.com');
        $this->line('');

        return 0;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
