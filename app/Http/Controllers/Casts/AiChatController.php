<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use App\Services\AiConciergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * キャストの「AI コンシェルジュ」自由入力チャットの受け口。
 *
 * 会話文の生成は LlmChatService（OSS モデル：Groq / Ollama 等の
 * OpenAI 互換 API）を優先し、失敗時は AiChatTemplateService に
 * 自動フォールバック。推薦カード自体は常に DB 由来の決定論的な選定なので、
 * LLM の hallucination で存在しないお店を紹介する心配はない。
 */
class AiChatController extends Controller
{
    public function respond(Request $request, AiConciergeService $concierge): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'history' => ['nullable', 'array', 'max:30'],
        ]);

        $historyIn = is_array($data['history'] ?? null) ? $data['history'] : [];
        // 各履歴要素の形を正規化: {role: user|ai|assistant, content: string}
        $history = [];
        foreach ($historyIn as $h) {
            if (!is_array($h)) continue;
            $role = (string) ($h['role'] ?? '');
            $content = (string) ($h['content'] ?? '');
            if ($role === '' || $content === '') continue;
            $history[] = ['role' => $role, 'content' => $content];
        }

        $response = $concierge->respond((string) $data['message'], $history);

        return response()->json([
            'success'         => true,
            'reply'           => $response['reply'],
            'recommendations' => $response['recommendations'],
            'quick_replies'   => $response['quick_replies'],
            'source'          => $response['source'] ?? 'template',
        ]);
    }
}
