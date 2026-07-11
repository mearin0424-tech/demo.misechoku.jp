<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use App\Services\AiConciergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $response = $concierge->respond(
            (string) $data['message'],
            $history,
            $this->currentCastPersonalityType(),
        );

        return response()->json([
            'success'         => true,
            'reply'           => $response['reply'],
            'recommendations' => $response['recommendations'],
            'quick_replies'   => $response['quick_replies'],
            'source'          => $response['source'] ?? 'template',
        ]);
    }

    /**
     * 登録済みの接客タイプ診断結果（例: LCIR）を取得。未登録・不正値は null。
     */
    private function currentCastPersonalityType(): ?string
    {
        $castId = (string) auth()->guard('member')->id();
        if ($castId === '' || !Schema::hasTable('cast_profiles')) {
            return null;
        }

        $row = DB::table('cast_profiles')
            ->where('cast_id', $castId)
            ->select(
                Schema::hasColumn('cast_profiles', 'personality_type')
                    ? 'personality_type'
                    : DB::raw('NULL as personality_type'),
                Schema::hasColumn('cast_profiles', 'memo')
                    ? 'memo'
                    : DB::raw('NULL as memo')
            )
            ->first();
        if (!$row) {
            return null;
        }

        $type = $row->personality_type ?? null;
        if (!is_string($type) || $type === '') {
            $memo = json_decode((string) ($row->memo ?? ''), true);
            $type = is_array($memo) ? ($memo['personality_type'] ?? null) : null;
        }

        return is_string($type) && preg_match('/^[LF][CP][IO][HR]$/', $type) ? $type : null;
    }
}
