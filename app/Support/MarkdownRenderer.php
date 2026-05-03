<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * 規約・ポリシー等の Markdown を HTML に変換する（Laravel Str::markdown / CommonMark）。
 */
final class MarkdownRenderer
{
    public static function toHtml(?string $markdown): string
    {
        $markdown = trim((string) $markdown);
        if ($markdown === '') {
            return '';
        }

        $result = Str::markdown($markdown);

        return $result instanceof HtmlString ? $result->toHtml() : (string) $result;
    }
}
