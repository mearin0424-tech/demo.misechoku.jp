<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:icons
                            {--force : Overwrite existing files}';
    protected $description = 'Generate 192x192 and 512x512 PNG icons for PWA installability (Chrome/Android)';

    private const DARK = [0x19, 0x05, 0x09];   // #190509
    private const GOLD = [0xD4, 0xAF, 0x37];  // #D4AF37

    public function handle(): int
    {
        $dir = public_path('assets/images/pwa');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $useGd = extension_loaded('gd');

        foreach ([192, 512] as $size) {
            $path = $dir . "/icon-{$size}.png";
            if (File::exists($path) && !$this->option('force')) {
                $this->line("Skip (exists): icon-{$size}.png");
                continue;
            }

            $png = $useGd ? $this->createWithGd($size) : $this->createWithPhp($size);
            if ($png === null) {
                $this->error("Failed to create icon-{$size}.png");
                return 1;
            }

            if (File::put($path, $png) === false) {
                $this->error("Failed to write {$path}");
                return 1;
            }
            $this->info("Created: icon-{$size}.png");
        }

        $this->line('');
        $this->info('PWA icons ready. Reload the app and use "Install" / "Add to Home Screen" for a full app install.');
        return 0;
    }

    /** PNG を GD で生成（見た目良好） */
    private function createWithGd(int $size): ?string
    {
        $img = @imagecreatetruecolor($size, $size);
        if ($img === false) {
            return null;
        }
        $dark = imagecolorallocate($img, self::DARK[0], self::DARK[1], self::DARK[2]);
        $gold = imagecolorallocate($img, self::GOLD[0], self::GOLD[1], self::GOLD[2]);
        imagefill($img, 0, 0, $dark);
        $cx = (int)($size / 2);
        $cy = (int)($size / 2);
        $radius = (int) round($size * 0.35);
        imagefilledellipse($img, $cx, $cy, $radius * 2, $radius * 2, $gold);
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        return $png ?: null;
    }

    /** GD なし: 純 PHP で単色＋中央円の PNG を生成 */
    private function createWithPhp(int $size): ?string
    {
        $cx = (int)($size / 2);
        $cy = (int)($size / 2);
        $r = (int) round($size * 0.35);

        $raw = '';
        for ($y = 0; $y < $size; $y++) {
            $raw .= "\x00"; // filter byte
            for ($x = 0; $x < $size; $x++) {
                $dx = $x - $cx;
                $dy = $y - $cy;
                $inCircle = ($dx * $dx + $dy * $dy) <= $r * $r;
                if ($inCircle) {
                    $raw .= chr(self::GOLD[0]) . chr(self::GOLD[1]) . chr(self::GOLD[2]);
                } else {
                    $raw .= chr(self::DARK[0]) . chr(self::DARK[1]) . chr(self::DARK[2]);
                }
            }
        }

        $idat = gzdeflate($raw, 9); // PNG は deflate のみ (zlib ヘッダなし)
        if ($idat === false) {
            return null;
        }

        $signature = "\x89PNG\r\n\x1a\n";
        $ihdr = $this->pngChunk('IHDR', pack('N2C5', $size, $size, 8, 2, 0, 0, 0));
        $idatChunk = $this->pngChunk('IDAT', $idat);
        $iend = $this->pngChunk('IEND', '');

        return $signature . $ihdr . $idatChunk . $iend;
    }

    private function pngChunk(string $type, string $data): string
    {
        $len = pack('N', strlen($data));
        $crc = pack('N', crc32($type . $data));
        return $len . $type . $data . $crc;
    }
}
