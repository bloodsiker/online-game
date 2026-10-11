<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Requests;

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
}
