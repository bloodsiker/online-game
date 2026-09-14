<?php

declare(strict_types=1);

namespace App\Modules\Share\Application\UseCases\Admin;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteShareItem
{
    public function execute(ShareItem $item): bool
    {
        $images = array_unique(array_filter([
            $item->getRawOriginal('image'),
            $item->getRawOriginal('transparent_image'),
        ]));

        try {
            DB::transaction(static fn (): bool => $item->delete());
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return false;
        }

        foreach ($images as $image) {
            $this->deleteImageUnlessShared($image);
        }

        return true;
    }

    private function deleteImageUnlessShared(string $path): void
    {
        if ($path === '' || str_starts_with($path, '/') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $isShared = ShareItem::query()
            ->where('image', $path)
            ->orWhere('transparent_image', $path)
            ->exists();

        if (! $isShared) {
            Storage::disk('public')->delete($path);
        }
    }
}
