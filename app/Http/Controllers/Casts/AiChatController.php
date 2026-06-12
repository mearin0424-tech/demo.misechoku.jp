<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use App\Services\AiChatTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * キャストの「AIレコメンド」自由入力チャットの受け口。
 * 本物の LLM ではなく、AiChatTemplateService がインテントを抽出してテンプレ返答する。
 */
class AiChatController extends Controller
{
    public function respond(Request $request, AiChatTemplateService $chat): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'history' => ['nullable', 'array', 'max:30'],
        ]);

        $response = $chat->respond(
            (string) $data['message'],
            is_array($data['history'] ?? null) ? $data['history'] : []
        );

        return response()->json([
            'success' => true,
            'reply'           => $response['reply'],
            'recommendations' => $response['recommendations'],
            'quick_replies'   => $response['quick_replies'],
        ]);
    }
}
