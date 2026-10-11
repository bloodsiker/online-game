<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Services;

use App\Modules\User\Domain\Settings\GameSettingsDefinition;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Infrastructure\Persistence\Models\UserGameSetting;
use Illuminate\Support\Facades\DB;

final class GameSettingsService
{
    public function get(int $userId): array
    {
        $stored = UserGameSetting::query()->where('user_id', $userId)->first();

        return array_replace(GameSettingsDefinition::defaults(), GameSettingsDefinition::normalize($stored?->settings ?? []));
    }

    public function update(int $userId, array $values): array
    {
        return DB::transaction(function () use ($userId, $values): array {
            // Lock the owner even when the settings row doesn't exist yet.
            User::query()->without('player')->lockForUpdate()->findOrFail($userId);
            $settings = array_replace($this->get($userId), GameSettingsDefinition::normalize($values));
            UserGameSetting::query()->updateOrCreate(['user_id' => $userId], ['settings' => $settings]);

            return $settings;
        });
    }
}
