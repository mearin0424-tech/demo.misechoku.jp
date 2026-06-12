<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 検索結果のレレバンス（一致度）スコアリングを集中管理するサービス。
 *
 * 設計方針:
 *  - 既存の「ハードAND絞り込み」はそのまま残し、その上に「一致度スコア」を載せる
 *  - 'relevance' ソートでこのスコアを使って並べ替える
 *  - キーワードはフィールド別に重み付け（店舗名・ニックネームを最重視）
 *  - 店舗→キャストでは shop_search_preferences の「保存済み条件」を全てスコアに反映する
 *    （これまでは保存だけして検索に効いていなかった条件＝年齢/タグ/出勤頻度/勤務時間帯/夜職経験）
 */
class SearchScoringService
{
    /**
     * キーワードを語にトークナイズ（半角/全角空白）。
     *
     * @return array<int, string>
     */
    public function tokenize(string $normalizedKeyword): array
    {
        if ($normalizedKeyword === '') {
            return [];
        }
        $tokens = preg_split('/\s+/u', $normalizedKeyword) ?: [];
        return array_values(array_filter($tokens, static fn ($t) => $t !== ''));
    }

    /**
     * フィールド重み付きキーワードスコア。
     *
     * @param array<int, array{0: string, 1: int}> $weightedFields  [normalized_text, weight]
     * @param array<int, string> $tokens
     */
    public function weightedKeywordScore(array $weightedFields, array $tokens): int
    {
        if ($tokens === []) {
            return 0;
        }
        $score = 0;
        foreach ($weightedFields as [$text, $weight]) {
            if ($text === '' || $weight <= 0) {
                continue;
            }
            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }
                if (str_contains($text, $token)) {
                    $score += $weight;
                }
            }
        }
        return $score;
    }

    /**
     * 店舗 → キャスト の一致度スコア。
     *
     * 店舗が `shop_search_preferences` に保存している条件（年齢/容姿タグ/性格タグ/
     * 出勤頻度/勤務時間帯/夜職経験）を、それぞれ重み付きで加点する。
     *
     * @param object $castRow                Eloquent DB::table 由来の行（nickname/name/birthday/exp 等を持つ）
     * @param array<string, mixed> $context  以下のキーを取る:
     *   - keywordTokens:  array<int, string>
     *   - normalize:      callable(string): string
     *   - prefs:          shop_search_preferences の loadAll() 形
     *   - castTagsByCastId: array<string, array{looks: int[], personality: int[]}>
     *   - castPrefsByCastId: array<string, array{shift_frequency: ?string, work_periods: string[]}>
     *
     * @return array{score: int, matched: int, total: int, reasons: array<int, string>}
     */
    public function scoreCastRow(object $castRow, array $context): array
    {
        $score = 0;
        $matched = 0;
        $total = 0;
        $reasons = [];

        $normalize = $context['normalize'] ?? static fn (string $s) => mb_strtolower($s);
        $tokens = $context['keywordTokens'] ?? [];
        $prefs  = $context['prefs'] ?? [];

        // 1) キーワード — フィールド重み付け（ニックネーム最重視）
        if (!empty($tokens)) {
            $kwFields = [
                [$normalize((string) ($castRow->nickname ?? '')), 10],
                [$normalize((string) ($castRow->name ?? '')),      5],
                [$normalize((string) ($castRow->pref ?? '')),      3],
                [$normalize((string) ($castRow->city ?? '')),      3],
                [$normalize((string) ($castRow->hitokoto_body ?? '')), 2],
                [$normalize((string) ($castRow->pr ?? '')),        1],
            ];
            $kwScore = $this->weightedKeywordScore($kwFields, $tokens);
            if ($kwScore > 0) {
                $score += $kwScore;
                $reasons[] = 'キーワード一致';
            }
        }

        // 2) 年齢レンジ
        if (($prefs['age_min'] ?? null) !== null || ($prefs['age_max'] ?? null) !== null) {
            $total++;
            $age = !empty($castRow->birthday) ? Carbon::parse($castRow->birthday)->age : null;
            if ($age !== null) {
                $okMin = ($prefs['age_min'] ?? null) === null || $age >= (int) $prefs['age_min'];
                $okMax = ($prefs['age_max'] ?? null) === null || $age <= (int) $prefs['age_max'];
                if ($okMin && $okMax) {
                    $score += 8;
                    $matched++;
                    $reasons[] = "年齢一致 ({$age}歳)";
                }
            }
        }

        // 3) 容姿タグ
        $castId = (string) ($castRow->id ?? '');
        $tagsForCast = $context['castTagsByCastId'][$castId] ?? ['looks' => [], 'personality' => []];

        if (!empty($prefs['looks_tag_ids'])) {
            $total++;
            $overlap = array_values(array_intersect(
                $tagsForCast['looks'],
                array_map('intval', $prefs['looks_tag_ids'])
            ));
            if ($overlap !== []) {
                $score += 6 * count($overlap);
                $matched++;
                $reasons[] = '容姿タグ一致 (' . count($overlap) . ')';
            }
        }

        // 4) 性格タグ
        if (!empty($prefs['personality_tag_ids'])) {
            $total++;
            $overlap = array_values(array_intersect(
                $tagsForCast['personality'],
                array_map('intval', $prefs['personality_tag_ids'])
            ));
            if ($overlap !== []) {
                $score += 6 * count($overlap);
                $matched++;
                $reasons[] = '性格タグ一致 (' . count($overlap) . ')';
            }
        }

        // 5) 出勤頻度
        $castPrefs = $context['castPrefsByCastId'][$castId] ?? null;
        if (!empty($prefs['shift_frequency'])) {
            $total++;
            if ($castPrefs && ($castPrefs['shift_frequency'] ?? null) === $prefs['shift_frequency']) {
                $score += 5;
                $matched++;
                $reasons[] = '出勤頻度一致';
            }
        }

        // 6) 勤務時間帯
        if (!empty($prefs['work_periods'])) {
            $total++;
            if ($castPrefs && !empty($castPrefs['work_periods'])) {
                $overlap = array_values(array_intersect(
                    (array) $prefs['work_periods'],
                    (array) $castPrefs['work_periods']
                ));
                if ($overlap !== []) {
                    $score += 3 * count($overlap);
                    $matched++;
                    $reasons[] = '勤務時間帯一致 (' . count($overlap) . ')';
                }
            }
        }

        // 7) 夜職経験
        if (!empty($prefs['night_work_exp']) && $prefs['night_work_exp'] !== 'any') {
            $total++;
            $castExp = ((int) ($castRow->exp ?? 0) === 1) ? 'yes' : 'none';
            if ($castExp === $prefs['night_work_exp']) {
                $score += 4;
                $matched++;
                $reasons[] = '夜職経験一致';
            }
        }

        return [
            'score'   => $score,
            'matched' => $matched,
            'total'   => $total,
            'reasons' => $reasons,
        ];
    }

    /**
     * キャスト → 店舗 の一致度スコア（キーワード重み付け中心）。
     *
     * 既存のフォーム条件（業種/エリア/時給/採用報酬/タグ）はハードフィルタで通過済みの
     * ものを受け取る前提。ここではキーワードの重み付きスコアを返す。
     *
     * @param object $shopRow                shop_name/pref/city/shop_post_body などを持つ行
     * @param array<string, mixed> $context  keywordTokens, normalize
     *
     * @return array{score: int, matched: int, total: int, reasons: array<int, string>}
     */
    public function scoreShopRow(object $shopRow, array $context): array
    {
        $score = 0;
        $reasons = [];

        $normalize = $context['normalize'] ?? static fn (string $s) => mb_strtolower($s);
        $tokens = $context['keywordTokens'] ?? [];

        if (!empty($tokens)) {
            $kwFields = [
                [$normalize((string) ($shopRow->shop_name ?? '')),        10],
                [$normalize((string) ($shopRow->pref ?? '')),              3],
                [$normalize((string) ($shopRow->city ?? '')),              3],
                [$normalize((string) ($shopRow->shop_post_body ?? '')),    2],
            ];
            $kwScore = $this->weightedKeywordScore($kwFields, $tokens);
            if ($kwScore > 0) {
                $score += $kwScore;
                $reasons[] = 'キーワード一致';
            }
        }

        return [
            'score'   => $score,
            'matched' => 0,
            'total'   => 0,
            'reasons' => $reasons,
        ];
    }

    // ===== バッチローダ =====

    /**
     * 複数キャストのタグ（looks/personality）をまとめて取得し、cast_id 連想配列で返す。
     *
     * @param array<int, string> $castIds
     * @return array<string, array{looks: int[], personality: int[]}>
     */
    public function loadCastTagsByCastIds(array $castIds): array
    {
        if ($castIds === [] || !Schema::hasTable('cast_tag_relations')) {
            return [];
        }
        $rows = DB::table('cast_tag_relations')
            ->whereIn('cast_id', $castIds)
            ->whereIn('tag_type', ['looks', 'personality'])
            ->get(['cast_id', 'tag_id', 'tag_type']);

        $out = [];
        foreach ($castIds as $id) {
            $out[(string) $id] = ['looks' => [], 'personality' => []];
        }
        foreach ($rows as $row) {
            $cid = (string) $row->cast_id;
            $cat = (string) $row->tag_type;
            if (!isset($out[$cid][$cat])) {
                continue;
            }
            $out[$cid][$cat][] = (int) $row->tag_id;
        }
        return $out;
    }

    /**
     * 複数キャストの cast_search_preferences をまとめて取得し、cast_id 連想配列で返す。
     * shift_frequency / work_periods のみ参照。
     *
     * @param array<int, string> $castIds
     * @return array<string, array{shift_frequency: ?string, work_periods: array<int, string>}>
     */
    public function loadCastPrefsByCastIds(array $castIds): array
    {
        if ($castIds === [] || !Schema::hasTable('cast_search_preferences')) {
            return [];
        }
        $rows = DB::table('cast_search_preferences')
            ->whereIn('cast_id', $castIds)
            ->get(['cast_id', 'shift_frequency', 'work_periods']);

        $out = [];
        foreach ($rows as $row) {
            $wp = [];
            if (!empty($row->work_periods)) {
                $decoded = json_decode((string) $row->work_periods, true);
                if (is_array($decoded)) {
                    $wp = array_values(array_filter(array_map(
                        static fn ($v) => is_string($v) ? $v : null,
                        $decoded
                    )));
                }
            }
            $out[(string) $row->cast_id] = [
                'shift_frequency' => $row->shift_frequency ?? null,
                'work_periods'    => $wp,
            ];
        }
        return $out;
    }
}
