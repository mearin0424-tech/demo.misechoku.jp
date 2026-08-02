<?php

namespace Tests\Feature\Support;

use App\Services\NgWordDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * NG-word detection unit-level tests (via app container).
 * Covers regex patterns for contact-info leaks + free-form word list.
 */
class NgWordDetectorTest extends TestCase
{
    use RefreshDatabase;

    private NgWordDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('talk:ng_words');
        $this->detector = app(NgWordDetector::class);
    }

    /** @test */
    public function empty_text_is_clean(): void
    {
        $this->assertNull($this->detector->detect(''));
    }

    /** @test */
    public function plain_text_is_clean(): void
    {
        $this->assertNull($this->detector->detect('よろしくお願いします！'));
    }

    /**
     * @test
     * @dataProvider contactLeakSamples
     */
    public function detects_contact_info_leak(string $text, string $expectedLabel): void
    {
        $hit = $this->detector->detect($text);
        $this->assertNotNull($hit, "Expected NG match for: $text");
        $this->assertSame($expectedLabel, $hit);
    }

    public static function contactLeakSamples(): array
    {
        return [
            'landline'      => ['連絡先: 03-1234-5678', '電話番号'],
            'mobile'        => ['09012345678 に連絡してください', '携帯番号'],
            'email'         => ['user.name+tag@example.co.jp', 'メールアドレス'],
            'http url'      => ['詳しくは http://example.com を', 'URL'],
            'https url'     => ['プロフィール https://line.me/xxx', 'URL'],
            'line id'       => ['LINE ID: my_line_id_123', 'LINE ID'],
            'sns handle'    => ['DM ください @cutestagram', 'SNSアカウント'],
        ];
    }

    /** @test */
    public function detects_word_from_ng_words_table(): void
    {
        DB::table('ng_words')->insert([
            ['word' => 'テストNG語', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        Cache::forget('talk:ng_words');

        $this->assertSame('テストNG語', $this->detector->detect('この店にはテストNG語が含まれています'));
    }

    /** @test */
    public function inactive_ng_words_are_ignored(): void
    {
        DB::table('ng_words')->insert([
            ['word' => '無効ワード', 'is_active' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
        Cache::forget('talk:ng_words');

        $this->assertNull($this->detector->detect('無効ワードを含む文章'));
    }

    /** @test */
    public function patternsForFront_returns_regex_label_pairs(): void
    {
        $patterns = $this->detector->patternsForFront();
        $this->assertNotEmpty($patterns);
        foreach ($patterns as $p) {
            $this->assertArrayHasKey('regex', $p);
            $this->assertArrayHasKey('label', $p);
        }
    }
}
