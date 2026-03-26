<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ColumnArticleStorageService
{
    private const RELATIVE_PREFIX = 'uploads/columns';

    public function store(UploadedFile $file): string
    {
        $dir = public_path(self::RELATIVE_PREFIX);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! preg_match('/^[a-z0-9]{1,8}$/', $ext)) {
            $ext = 'jpg';
        }

        $name = Str::uuid()->toString() . '.' . $ext;
        $file->move($dir, $name);

        return self::RELATIVE_PREFIX . '/' . $name;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (! str_starts_with($path, self::RELATIVE_PREFIX . '/')) {
            return;
        }

        $full = public_path($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
