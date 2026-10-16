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
        $key = 'game_settings.'.$userId;
        if (request()->attributes->has($key)) {
            return request()->attributes->get($key);
        }
        $stored = UserGameSetting::query()->where('user_id', $userId)->first();

        $settings = array_replace(GameSettingsDefinition::defaults(), GameSettingsDefinition::normalize($stored?->settings ?? []));
        request()->attributes->set($key, $settings);

        return $settings;
    }

    public function update(int $userId, array $values): array
    {
        $settings = DB::transaction(function () use ($userId, $values): array {
            // Lock the owner even when the settings row doesn't exist yet.
            User::query()->without('player')->lockForUpdate()->findOrFail($userId);
            request()->attributes->remove('game_settings.'.$userId);
            $settings = array_replace($this->get($userId), GameSettingsDefinition::normalize($values));
            UserGameSetting::query()->updateOrCreate(['user_id' => $userId], ['settings' => $settings]);

            return $settings;
        });
        request()->attributes->set('game_settings.'.$userId, $settings);

        return $settings;
    }
}
