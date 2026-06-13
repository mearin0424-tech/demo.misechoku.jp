<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * LINE Messaging API でテキストメッセージを送信する薄いラッパー。
 *
 * SDK (linecorp/line-bot-sdk) が未インストール、または channel_token が未設定の環境でも
 * クラスが解決可能な状態を維持できるよう、SDK のインスタンス化は **遅延 (lazy) 評価** とし、
 * 失敗時はログを残して `success=false` を返す。
 *
 * 利用元: LineNotificationService（コンストラクタで DI される）
 */
class LineMessageService
{
    /** @var \LINE\LINEBot|null */
    protected $bot = null;

    /**
     * SDK が使えない・トークン未設定なら null を返す。それ以外はキャッシュ済みインスタンスを返す。
     *
     * @return \LINE\LINEBot|null
     */
    protected function bot()
    {
        if ($this->bot !== null) {
            return $this->bot;
        }
        // SDK (linecorp/line-bot-sdk) 未インストール環境では安全に no-op で動く
        if (!class_exists('\LINE\LINEBot') || !class_exists('\LINE\LINEBot\HTTPClient\CurlHTTPClient')) {
            return null;
        }
        $token  = (string) config('services.line.message.channel_token', '');
        $secret = (string) config('services.line.message.channel_secret', '');
        if ($token === '' || $secret === '') {
            return null;
        }

        $httpClientClass = '\\LINE\\LINEBot\\HTTPClient\\CurlHTTPClient';
        $botClass        = '\\LINE\\LINEBot';
        $this->bot = new $botClass(new $httpClientClass($token), ['channelSecret' => $secret]);
        return $this->bot;
    }

    /**
     * @return array{success: bool, error?: string}
     */
    public function sendText(string $lineUserId, string $message): array
    {
        $bot = $this->bot();
        if ($bot === null) {
            return ['success' => false, 'error' => 'line_sdk_or_config_missing'];
        }
        try {
            $textBuilderClass = '\\LINE\\LINEBot\\MessageBuilder\\TextMessageBuilder';
            $textMessageBuilder = new $textBuilderClass($message);
            $response = $bot->pushMessage($lineUserId, $textMessageBuilder);
            $ok = is_object($response) && method_exists($response, 'isSucceeded')
                ? (bool) $response->isSucceeded()
                : true;
            return ['success' => $ok];
        } catch (\Throwable $e) {
            Log::warning('LineMessageService::sendText failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'send_failed'];
        }
    }
}
