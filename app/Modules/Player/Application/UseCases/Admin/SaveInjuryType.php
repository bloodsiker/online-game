<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases\Admin;

use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class SaveInjuryType
{
    private AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function execute(
        InjuryType $injuryType,
        array $data,
        ?UploadedFile $image,
        bool $deleteImage,
    ): InjuryType {
        $oldImage = $injuryType->getRawOriginal('image');

        $injuryType->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'body_part' => $data['body_part'],
            'severity' => (int) $data['severity'],
            'duration_seconds' => (int) $data['duration_minutes'] * 60,
            'drop_weight' => (int) $data['drop_weight'],
            'stat_modifiers' => $this->normalizeModifiers($data['stat_modifiers'] ?? []),
            'is_active' => (bool) $data['is_active'],
        ]);

        if ($image !== null) {
            $injuryType->image = $this->imageStorage->storeOnPublicDisk($image, 'injuries');
        } elseif ($deleteImage) {
            $injuryType->image = null;
        }

        $injuryType->save();

        if (($image !== null || $deleteImage) && $oldImage !== $injuryType->getRawOriginal('image')) {
            $this->deleteStorageImage($oldImage);
        }

        return $injuryType;
    }

    private function normalizeModifiers(array $modifiers): array
    {
        return collect($modifiers)
            ->filter(static fn (mixed $modifier): bool => is_array($modifier) && filled($modifier['stat'] ?? null))
            ->map(static fn (array $modifier): array => [
                'stat' => (string) $modifier['stat'],
                'value' => (float) $modifier['value'],
                'is_percent' => (bool) $modifier['is_percent'],
            ])
            ->values()
            ->all();
    }

    private function deleteStorageImage(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, '/')
            || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
