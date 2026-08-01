<?php

namespace App\Services;

/**
 * AI コンシェルジュのオーケストレータ。
 *
 * 「候補店舗選定は決定論的（AiChatTemplateService）」+
 * 「自然な会話文生成は LLM（LlmChatService）」というハイブリッド構成。
 *
 * LLM が無効／失敗時は自動でテンプレ返答にフォールバックする。
 * 推薦カード（recommendations）は常に DB 由来なので、LLM の hallucination で
 * 存在しないお店を紹介するリスクを回避する。
 */
class AiConciergeService
{
    /**
     * コンシェルジュのシステムプロンプト。
     * 口調・出力形式・禁止事項をここで固定する。
     */
    private const SYSTEM_PROMPT = <<<'PROMPT'
あなたはミセチョク（水商売・夜職向けのマッチングアプリ）に常駐する
「AI コンシェルジュ」です。求職者（キャスト）の相談を受けて、
おすすめのお店を紹介します。

# キャラクター
- 一人称は「私」または名前なし。夜職の先輩のような、親身で明るい口調。
- 語尾はカジュアル。文末に絵文字（✨💎🌸💰🌙🎉☕🥺 など）を1文につき1つまで
  自然に添える。過剰にしない。
- 敬語は避け、フレンドリーな「〜だよ」「〜だね」「〜してみて」を基本にする。
- 相手を励ますトーンを保つ。「未経験でも大丈夫」「一緒に探そう」など。

# タスク
- ユーザーの希望（エリア／業種／時給／未経験可／ノルマの有無／雰囲気 など）を
  読み取り、それに合う候補店舗を提示する。
- 候補店舗のリストは開発者から「### 候補店舗（この中からのみ紹介できる）」として
  与えられる。それ以外のお店の名前を出してはいけない。
- 開発者からの補足に「条件を緩めて出した」旨が明記されているときは、
  必ず冒頭で「ちょうどぴったりのお店はまだ無かったから、○○を広げて近いところを出したよ」
  という正直で前向きなトーンを添えること（強要）。
- 候補が本当に 0 件のときは、条件を緩める提案をする（「エリアを変える？」等）。
- 会話履歴を踏まえて、繰り返しの挨拶にならないよう自然に続ける。

# 出力
- Markdown や見出しは使わない。日本語の話し言葉のみ。
- 100〜200 文字程度に収める。カード（お店の詳細）は別途 UI で表示されるため、
  お店の値段や住所を長々と再掲する必要はない。
- 応答の末尾に「気になるお店ある？」「他の条件も試してみる？」など、
  次のアクションを促す軽い一言を添える。
- 候補が複数あるときは各店の魅力を "選ばれた理由" を軸に、1〜2文で並列に触れる。
- 絶対に電話番号・住所の詳細・年齢確認要件・法令に関わる助言は出さない。
- 医療・法律・税務の具体的助言は避け、必要なら「専門家に相談」と促す。
PROMPT;

    /**
     * 接客タイプ診断（4文字コード）の各軸の意味。
     * LLM が「私のタイプに合うお店」等の相談に答えられるようコンテキストに添える。
     */
    private const PERSONALITY_AXES = [
        'L' => 'リード型（会話を主導して場を盛り上げる）',
        'F' => 'フォロワー型（聞き役でお客様のペースに合わせる）',
        'C' => '恋人型（女性らしさ・疑似恋愛が武器）',
        'P' => 'パートナー型（知性と対等な会話が武器）',
        'I' => '懐型（人懐っこく一気に距離を詰める）',
        'O' => '領域型（プロの距離感・ミステリアスさを保つ）',
        'H' => 'ハンター型（短期集中で大きな結果を出す）',
        'R' => 'リレーション型（マメな連絡で関係をじっくり育てる）',
    ];

    public function __construct(
        private readonly AiChatTemplateService $template,
        private readonly LlmChatService $llm,
    ) {
    }

    /**
     * ユーザ発話に返答する。
     *
     * @param  string $userMessage
     * @param  array<int, array{role:string, content:string}> $history
     * @param  ?string $personalityType 登録済みの接客タイプ診断（例: LCIR）
     * @return array{reply: string, recommendations: array<int, array<string,mixed>>, quick_replies: array<int, string>, source: 'llm'|'template'}
     */
    public function respond(string $userMessage, array $history = [], ?string $personalityType = null): array
    {
        $userMessage = trim($userMessage);
        if ($userMessage === '') {
            $g = $this->template->respond('');
            $g['source'] = 'template';
            return $g;
        }

        // 1) 常に決定論的に intent と店舗候補を作る（LLM の hallucination 回避）
        $grounded = $this->template->buildGroundedContext($userMessage, 3);
        $intent = $grounded['intent'];
        $recs   = $grounded['recommendations'];
        $relaxed = $grounded['relaxed'] ?? [];

        // 2) LLM を試す（失敗時は null）
        $llmReply = null;
        if ($this->llm->isEnabled()) {
            $llmReply = $this->llm->chat(
                self::SYSTEM_PROMPT,
                $this->buildMessages($userMessage, $history, $intent, $recs, $personalityType, $relaxed),
            );
        }

        // 3) reply の最終決定
        if ($llmReply !== null && $llmReply !== '') {
            return [
                'reply'           => $this->sanitizeLlmReply($llmReply),
                'recommendations' => $recs,
                'quick_replies'   => $this->template->buildQuickReplies($intent),
                'source'          => 'llm',
            ];
        }

        // 4) LLM 不使用／失敗 → テンプレ
        $t = $this->template->respond($userMessage, $history);
        $t['source'] = 'template';
        return $t;
    }

    /**
     * LLM に渡す messages を組む。履歴は role=user/assistant の平文で送る。
     *
     * @param  array<int, array{role:string, content:string}> $history
     * @param  array<string,mixed> $intent
     * @param  array<int, array<string,mixed>> $recs
     * @return array<int, array{role:string, content:string}>
     */
    private function buildMessages(string $userMessage, array $history, array $intent, array $recs, ?string $personalityType = null, array $relaxed = []): array
    {
        $out = [];

        // 履歴（user/assistant のみ、role の名寄せ）
        foreach ($history as $h) {
            $role = (string) ($h['role'] ?? '');
            $role = match ($role) {
                'ai', 'assistant' => 'assistant',
                'user'            => 'user',
                default           => null,
            };
            if ($role === null) continue;
            $content = trim((string) ($h['content'] ?? ''));
            if ($content === '') continue;
            $out[] = ['role' => $role, 'content' => $content];
        }

        // 直近のユーザ入力（重複を避けるため、末尾が同じなら追加しない）
        $lastUserPushed = end($out);
        if ($lastUserPushed === false || ($lastUserPushed['role'] ?? '') !== 'user'
            || ($lastUserPushed['content'] ?? '') !== $userMessage) {
            $out[] = ['role' => 'user', 'content' => $userMessage];
        }

        // 候補店舗を "developer / system 補足" として最後の user メッセージに
        // インジェクション形式で添える（履歴に残さない）
        $context = $this->formatShopsContext($intent, $recs, $personalityType, $relaxed);
        if ($context !== '') {
            // 末尾の user メッセージにコンテキストを追加
            $lastIdx = count($out) - 1;
            $out[$lastIdx]['content'] = $out[$lastIdx]['content']
                . "\n\n---\n" . $context;
        }

        return $out;
    }

    /**
     * @param  array<string,mixed> $intent
     * @param  array<int, array<string,mixed>> $recs
     */
    private function formatShopsContext(array $intent, array $recs, ?string $personalityType = null, array $relaxed = []): string
    {
        $lines = [];
        $lines[] = '（開発者からの補足。ユーザには見えない）';
        $lines[] = '解析された希望条件: ' . $this->intentSummary($intent);
        if ($personalityType !== null && $personalityType !== '') {
            $axes = array_filter(array_map(
                fn (string $c) => self::PERSONALITY_AXES[$c] ?? null,
                str_split($personalityType),
            ));
            $lines[] = '相談者の接客タイプ診断: ' . $personalityType
                . '（' . implode(' / ', $axes) . '）'
                . '。「私のタイプに合うお店」等と聞かれたらこの特性を踏まえて候補の魅力を語ること。';
        }

        // 段階緩和が入ったときは「どんぴしゃは無かった／近い候補を出している」旨を LLM に伝える
        if ($relaxed !== []) {
            if (in_array('all_filters', $relaxed, true)) {
                $lines[] = '重要: 条件どんぴしゃのお店は登録がなかったため、'
                    . '全条件を外して人気順・新着順で近いお店を出しています。'
                    . '返答の冒頭で「ちょうどぴったりのお店はまだないから、いま近そうなお店を先に紹介するね」等の'
                    . '正直で前向きなトーンを必ず入れてください。';
            } else {
                $labelMap = [
                    'area'       => 'エリア',
                    'industry'   => '業種',
                    'wage'       => '時給の条件',
                    'reward_min' => '採用報酬の条件',
                    'atmosphere' => '雰囲気の条件',
                ];
                $labels = array_values(array_filter(array_map(fn ($k) => $labelMap[$k] ?? null, $relaxed)));
                $labelText = $labels === [] ? '条件' : implode('・', $labels);
                $lines[] = '重要: 条件どんぴしゃのお店が無かったため、【' . $labelText . '】を少し緩めて近い候補を出しています。'
                    . '返答の冒頭で「ぴったりのお店はまだないから、' . $labelText . 'を少し広げて近いところを出したよ」等、'
                    . '正直に伝える一言を必ず入れてください。';
            }
        }

        if (empty($recs)) {
            $lines[] = '### 候補店舗（この中からのみ紹介できる）';
            $lines[] = '（該当なし。「まだピッタリが無いから、また条件を教えてね」と前向きに促してください）';
        } else {
            $lines[] = '### 候補店舗（この中からのみ紹介できる）';
            foreach ($recs as $i => $r) {
                $n = $i + 1;
                $area = trim(($r['pref'] ?? '') . ' ' . ($r['city'] ?? ''));
                $wage = !empty($r['wage'])
                    ? '時給 ' . number_format((int) $r['wage']) . '円〜'
                    : '時給情報なし';
                $reward = !empty($r['reward'])
                    ? '入店報酬 ' . number_format((int) $r['reward']) . '円'
                    : '';
                $reason = trim((string) ($r['reason'] ?? ''));
                $bits = array_filter([$area, $wage, $reward, $reason]);
                $lines[] = "{$n}. {$r['name']} — " . implode(' / ', $bits);
            }
        }
        $lines[] = '';
        $lines[] = '上記の候補を踏まえて、自然な口調で 100〜200 字の返答を1つだけ生成してください。';
        return implode("\n", $lines);
    }

    /**
     * @param array<string,mixed> $intent
     */
    private function intentSummary(array $intent): string
    {
        $bits = [];
        if (!empty($intent['area']))       $bits[] = 'エリア=' . $intent['area'];
        if (!empty($intent['industry']))   $bits[] = '業種='   . $intent['industry'];
        if (!empty($intent['wage_min']))   $bits[] = '時給下限=' . $intent['wage_min'];
        if (!empty($intent['reward_min'])) $bits[] = '報酬下限=' . $intent['reward_min'];
        if (!empty($intent['no_experience'])) $bits[] = '未経験OK希望';
        if (!empty($intent['no_norma']))      $bits[] = 'ノルマ緩め希望';
        if (!empty($intent['high_wage']))     $bits[] = '高時給希望';
        if (!empty($intent['near_station']))  $bits[] = '駅近希望';
        if (!empty($intent['atmosphere']))    $bits[] = '雰囲気=' . $intent['atmosphere'];
        return $bits === [] ? '（明確な指定なし）' : implode(', ', $bits);
    }

    /**
     * LLM の生返答から余計な要素（コードブロック、冗長な自己紹介、Markdown 見出し）を軽く除去。
     */
    private function sanitizeLlmReply(string $reply): string
    {
        // コードブロック除去
        $reply = preg_replace('/```[\s\S]*?```/', '', $reply) ?? $reply;
        // 見出し記号除去
        $reply = preg_replace('/^#+\s*/m', '', $reply) ?? $reply;
        // 連続空行を 1 行に
        $reply = preg_replace("/\n{3,}/", "\n\n", $reply) ?? $reply;
        // 長すぎる返答は 400 字で切る（保険）
        if (mb_strlen($reply) > 400) {
            $reply = mb_substr($reply, 0, 400) . '…';
        }
        return trim($reply);
    }
}
