<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Infrastructure\Images;

use App\Modules\Telegram\Domain\Contracts\BackpackPreviewRenderer;
use GdImage;
use Illuminate\Support\Facades\Storage;

final class GdBackpackPreviewRenderer implements BackpackPreviewRenderer
{
    /** @param list<array<string, mixed>> $items */
    public function render(array $items): string
    {
        $canvas = imagecreatetruecolor(600, 324);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 245, 229, 194));
        $ink = imagecolorallocate($canvas, 83, 43, 22);
        $light = imagecolorallocate($canvas, 255, 247, 227);
        $badge = imagecolorallocate($canvas, 59, 39, 24);
        $white = imagecolorallocate($canvas, 255, 244, 211);
        $frame = $this->loadImage(asset('main/images/user-reward-frame.png'));
        foreach (range(0, 7) as $index) {
            $x = 20 + ($index % 4) * 146;
            $y = 18 + (int) floor($index / 4) * 154;
            imagefilledrectangle($canvas, $x, $y, $x + 120, $y + 120, $light);
            $item = $items[$index] ?? null;
            if ($item) {
                $source = $this->loadImage($item['image_url']);
                if ($source) {
                    $scale = min(104 / imagesx($source), 104 / imagesy($source));
                    $width = max(1, (int) round(imagesx($source) * $scale));
                    $height = max(1, (int) round(imagesy($source) * $scale));
                    imagecopyresampled($canvas, $source, $x + 8 + (int) ((104 - $width) / 2), $y + 8 + (int) ((104 - $height) / 2),
                        0, 0, $width, $height, imagesx($source), imagesy($source));
                    imagedestroy($source);
                } else {
                    imagestring($canvas, 5, $x + 56, $y + 50, '?', $ink);
                }
            }
            if ($frame) {
                imagecopyresampled($canvas, $frame, $x, $y, 0, 0, 120, 120, imagesx($frame), imagesy($frame));
            } else {
                imagerectangle($canvas, $x, $y, $x + 120, $y + 120, $ink);
            }
            if (! $item) {
                continue;
            }
            $color = preg_match('/^#[a-f0-9]{6}$/iD', $item['rarity_color'] ?? '') ? $item['rarity_color'] : '#666666';
            $rgb = sscanf($color, '#%02x%02x%02x');
            $rarity = imagecolorallocate($canvas, ...$rgb);
            imagerectangle($canvas, $x + 6, $y + 6, $x + 113, $y + 113, $rarity);
            $quantity = number_format((int) $item['quantity'], 0, '', ' ');
            $font = strlen($quantity) > 13 ? 2 : 4;
            $textWidth = strlen($quantity) * imagefontwidth($font);
            imagefilledrectangle($canvas, $x + 115 - $textWidth - 6, $y + 96, $x + 115, $y + 115, $badge);
            imagestring($canvas, $font, $x + 115 - $textWidth - 3, $y + 98, $quantity, $white);
            imagestring($canvas, 5, $x + 54, $y + 125, (string) ($index + 1), $ink);
            if (($item['upgrade_level'] ?? 0) > 0) {
                $upgrade = '+'.$item['upgrade_level'];
                imagefilledrectangle($canvas, $x + 7, $y + 7, $x + 11 + strlen($upgrade) * 9, $y + 27, $badge);
                imagestring($canvas, 5, $x + 9, $y + 9, $upgrade, $white);
            }
        }
        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);
        if ($frame) {
            imagedestroy($frame);
        }

        return $png;
    }

    private function loadImage(string $url): ?GdImage
    {
        // Read only local game assets. Never fetch arbitrary URLs or follow paths outside these roots.
        $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));
        if (str_contains($path, "\0")) {
            return null;
        }
        $root = realpath(Storage::disk('public')->path(''));
        $candidate = str_starts_with($path, '/storage/')
            ? Storage::disk('public')->path(substr($path, strlen('/storage/')))
            : public_path(ltrim($path, '/'));
        $real = realpath($candidate);
        $public = realpath(public_path());
        if (! $real || ! is_file($real)
            || ! (($root && str_starts_with($real, $root.DIRECTORY_SEPARATOR)) || ($public && str_starts_with($real, $public.DIRECTORY_SEPARATOR)))) {
            return null;
        }
        $bytes = @file_get_contents($real, length: 5 * 1024 * 1024 + 1);
        if (! is_string($bytes) || strlen($bytes) > 5 * 1024 * 1024) {
            return null;
        }
        $size = @getimagesizefromstring($bytes);
        if (! $size || $size[0] < 1 || $size[1] < 1 || $size[0] * $size[1] > 4000000) {
            return null;
        }

        return @imagecreatefromstring($bytes) ?: null;
    }
}
