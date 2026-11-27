<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Requests;

use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Domain\Settings\GameSettingsDefinition;
use App\Modules\User\Domain\Settings\HotkeyActions;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateGameSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return GameSettingsDefinition::rules();
    }

    public function after(): array
    {
        return [function ($validator): void {
            $this->validateHotkeyConflicts($validator);
        }, function ($validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->boolean('auto_loot_enabled')) {
                return;
            }
            if (! app(PremiumBonusService::class)->canAutoLoot((int) $this->user()->id)) {
                $validator->errors()->add('auto_loot_enabled', __('ui.auto_loot_requires_premium'));
            }
        }];
    }

    private function validateHotkeyConflicts($validator): void
    {
        if ($validator->errors()->isNotEmpty() || (! $this->has('hotkeys') && ! $this->has('magic_hotkeys'))) {
            return;
        }

        $stored = app(GameSettingsService::class)->get((int) $this->user()->id);
        $hotkeys = $this->has('hotkeys') ? (array) $this->input('hotkeys') : $stored['hotkeys'];
        $magicHotkeys = $this->has('magic_hotkeys') ? (array) $this->input('magic_hotkeys') : $stored['magic_hotkeys'];

        if (HotkeyActions::conflicts($hotkeys, $magicHotkeys) !== []) {
            $validator->errors()->add('hotkeys', __('ui.hotkeys_conflict'));
        }
    }
}
