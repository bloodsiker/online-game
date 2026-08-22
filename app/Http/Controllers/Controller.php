<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * Удаляет с диска 'public' старую картинку перед заменой на новую.
     *
     * Пропускает пустые значения и значения, не являющиеся чистым
     * относительным путём диска (готовые URL вида /storage/..., /img/..., http...) —
     * см. resolve_storage_image_url().
     */
    protected function deleteStorageImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
