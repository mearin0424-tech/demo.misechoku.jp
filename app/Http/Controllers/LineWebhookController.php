<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Messaging API の Webhook（LINE プラットフォームからの POST を受ける）
 *
 * LINE Login のコールバック（/login/line/callback）とは別 URL。LINE Developers の Webhook URL に
 * https://{ドメイン}/line/webhook を登録する。
 */
class LineWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) config('services.line.message.channel_secret');
        if ($secret === '') {
            Log::warning('LINE webhook: LINE_MESSAGE_CHANNEL_SECRET is empty');

            return response('Messaging API channel secret not configured', 503);
        }

        $body = $request->getContent();
        $signature = $request->header('X-Line-Signature');
        if ($signature === null || $signature === '') {
            Log::warning('LINE webhook: missing X-Line-Signature');

            return response('Bad Request', 400);
        }

        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));
        if (!hash_equals($expected, $signature)) {
            Log::warning('LINE webhook: invalid signature');

            return response('Bad Request', 400);
        }

        $payload = json_decode($body, true);
        if (is_array($payload) && !empty($payload['events'])) {
            Log::info('LINE webhook', ['event_count' => count($payload['events'])]);
        }

        return response('OK', 200);
    }
}
