<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI 互換の Chat Completions API を叩く共通クライアント。
 *
 * 主に Groq（無料枠が広い OSS モデルホスティング）を想定しつつ、
 * Ollama / Together AI / OpenRouter など OpenAI 互換 API を持つ他プロバイダも
 * 同じインターフェースでそのまま使える。
 *
 * 使い方:
 *
 *   $llm = app(LlmChatService::class);
 *   if ($llm->isEnabled()) {
 *       $reply = $llm->chat($systemPrompt, [
 *           ['role' => 'user', 'content' => 'こんにちは'],
 *       ]);
 *   }
 *
 * 設定は config/llm.php を参照。
 */
class LlmChatService
{
    /** @var array<string, mixed> */
    private array $config;

    public function __construct()
    {
        $this->config = (array) config('llm', []);
    }

    /**
     * LLM 連携が有効か（プロバイダ設定 + キーがある）。
     */
    public function isEnabled(): bool
    {
        $provider = (string) ($this->config['provider'] ?? 'template');
        if ($provider !== 'openai_compat') {
            return false;
        }
        $endpoint = trim((string) ($this->config['endpoint'] ?? ''));
        if ($endpoint === '') {
            return false;
        }
        // Ollama など認証不要な自ホストは api_key 空でも許容
        // → endpoint が localhost/内部 IP でない場合のみ key を必須にする
        $apiKey = trim((string) ($this->config['api_key'] ?? ''));
        if ($apiKey === '' && !$this->looksLikeLocalEndpoint($endpoint)) {
            return false;
        }
        return true;
    }

    /**
     * Chat Completions 呼び出し。返り値は生成された文字列（空文字含む）。
     * 例外は投げない。失敗時は null を返す（呼び出し側でフォールバック）。
     *
     * @param  string $systemPrompt     システムプロンプト（役割・口調・出力形式の指定）
     * @param  array<int, array{role:string, content:string}> $messages  ["role"=>"user"|"assistant","content"=>"..."]
     * @param  array<string, mixed> $overrides  temperature 等の一時上書き
     */
    public function chat(string $systemPrompt, array $messages, array $overrides = []): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $payload = [
            'model'       => (string) ($overrides['model']       ?? $this->config['model']       ?? 'llama-3.3-70b-versatile'),
            'temperature' => (float)  ($overrides['temperature'] ?? $this->config['temperature'] ?? 0.75),
            'top_p'       => (float)  ($overrides['top_p']       ?? $this->config['top_p']       ?? 0.95),
            'max_tokens'  => (int)    ($overrides['max_tokens']  ?? $this->config['max_tokens']  ?? 350),
            'stream'      => false,
            'messages'    => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $this->truncateHistory($messages)
            ),
        ];

        // OpenAI 互換 API で JSON レスポンス強制指定がしたい場合は response_format を渡せる
        if (!empty($overrides['response_format'])) {
            $payload['response_format'] = $overrides['response_format'];
        }

        $timeout = (int) ($this->config['timeout'] ?? 15);
        $endpoint = (string) $this->config['endpoint'];
        $apiKey   = (string) ($this->config['api_key'] ?? '');

        try {
            $req = Http::acceptJson()
                ->timeout($timeout)
                ->withHeaders(array_filter([
                    'Content-Type'  => 'application/json',
                    'Authorization' => $apiKey !== '' ? 'Bearer ' . $apiKey : null,
                ]));

            $res = $req->post($endpoint, $payload);

            if ($res->failed()) {
                Log::warning('LLM request failed', [
                    'status' => $res->status(),
                    'body'   => mb_strimwidth((string) $res->body(), 0, 400, '…'),
                ]);
                return null;
            }

            $data = $res->json();
            $content = data_get($data, 'choices.0.message.content');
            if (!is_string($content)) {
                return null;
            }
            return trim($content);
        } catch (Throwable $e) {
            Log::warning('LLM exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 履歴を許容ターン数で切り詰める（古い方から捨てる）。
     *
     * @param  array<int, array{role:string, content:string}> $messages
     * @return array<int, array{role:string, content:string}>
     */
    private function truncateHistory(array $messages): array
    {
        $maxTurns = (int) ($this->config['history_turns'] ?? 6);
        if ($maxTurns < 1) return $messages;

        // 1 ターン = user + assistant のペア想定なので概ね 2*max。
        $limit = $maxTurns * 2;
        if (count($messages) <= $limit) return $messages;

        return array_slice($messages, -1 * $limit);
    }

    private function looksLikeLocalEndpoint(string $endpoint): bool
    {
        return (bool) preg_match('~^https?://(localhost|127\.0\.0\.1|0\.0\.0\.0|10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)~i', $endpoint);
    }
}
