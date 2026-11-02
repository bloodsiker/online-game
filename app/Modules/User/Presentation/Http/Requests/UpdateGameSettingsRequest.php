<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Requests;

use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\User\Domain\Settings\GameSettingsDefinition;
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
            if ($validator->errors()->isNotEmpty() || ! $this->boolean('auto_loot_enabled')) {
                return;
            }
            if (! app(PremiumBonusService::class)->canAutoLoot((int) $this->user()->id)) {
                $validator->errors()->add('auto_loot_enabled', __('ui.auto_loot_requires_premium'));
            }
        }];
    }
}
