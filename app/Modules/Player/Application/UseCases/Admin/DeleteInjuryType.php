<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases\Admin;

use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteInjuryType
{
    public function execute(InjuryType $injuryType): bool
    {
        if ($injuryType->playerInjuries()->exists()) {
            return false;
        }

        $image = $injuryType->getRawOriginal('image');
        $injuryType->delete();

        if ($image !== null && $image !== '' && ! str_starts_with($image, '/')
            && ! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://')) {
            Storage::disk('public')->delete($image);
        }

        return true;
    }
}
