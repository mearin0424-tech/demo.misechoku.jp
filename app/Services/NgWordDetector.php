<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * NG-word detection for talk messages.
 *
 * Extracted from TalkController::detectNgWord() (2026-08-02) so that the rule
 * set can be unit-tested independently and reused (e.g. by admin moderation
 * tools or the review posting form).
 *
 * Two-layer detection:
 *   1. Regex patterns for contact-info leaks (phone, email, URL, LINE ID, SNS handles).
 *   2. Free-form word list from the `ng_words` table (cached 5 minutes).
 *
 * Returns the first matching label, or null if the text is clean.
 */
class NgWordDetector
{
    private const CACHE_KEY = 'talk:ng_words';
    private const CACHE_TTL_SECONDS = 300;

    /** @var array<string, string> regex => label */
    private const CONTACT_PATTERNS = [
        '/\b\d{2,4}-\d{2,4}-\d{4}\b/u'                       => '電話番号',
        '/(?:080|090|070|050)\d{8}/u'                        => '携帯番号',
        '/[\w.+-]+@[\w-]+\.[\w.-]+/u'                        => 'メールアドレス',
        '/https?:\/\/\S+/iu'                                 => 'URL',
        '/(?:line|ﾗｲﾝ|ライン)\s*(?:id|ID|アイディー)?[:：]?\s*[A-Za-z0-9._-]{3,}/iu' => 'LINE ID',
        '/@[A-Za-z0-9_.]{3,}/u'                              => 'SNSアカウント',
    ];

    /**
     * Detect an NG hit in the given text. Returns the matched label / word,
     * or null if the text is clean.
     */
    public function detect(string $text): ?string
    {
        if ($text === '') {
            return null;
        }
        $normalized = mb_convert_kana($text, 'asKV');

        // 1) contact-info leak regex
        foreach (self::CONTACT_PATTERNS as $regex => $label) {
            if (preg_match($regex, $normalized)) {
                return $label;
            }
        }

        // 2) ng_words table (cached)
        try {
            $words = Cache::remember(
                self::CACHE_KEY,
                self::CACHE_TTL_SECONDS,
                fn () => DB::table('ng_words')
                    ->where('is_active', 1)
                    ->pluck('word')
                    ->filter()
                    ->map(fn ($w) => (string) $w)
                    ->all()
            );
        } catch (\Throwable) {
            $words = [];
        }
        $needle = mb_strtolower($normalized);
        foreach ($words as $word) {
            $w = mb_strtolower(trim((string) $word));
            if ($w !== '' && mb_strpos($needle, $w) !== false) {
                return $word;
            }
        }
        return null;
    }

    /**
     * Convenience accessor for the regex list — used by the front-end warning UI
     * so that the same rules are enforced client-side (with server as the
     * authoritative check).
     *
     * @return array<int, array{regex: string, label: string}>
     */
    public function patternsForFront(): array
    {
        $out = [];
        foreach (self::CONTACT_PATTERNS as $regex => $label) {
            $out[] = ['regex' => $regex, 'label' => $label];
        }
        return $out;
    }
}
