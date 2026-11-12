<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http\Requests;

use App\Modules\User\Application\Services\PlayerVisibilityMaps;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMapPlayersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'map_id' => ['required', 'integer', Rule::in(array_keys(app(PlayerVisibilityMaps::class)->options()))],
            'hidden' => ['required', 'boolean'],
        ];
    }
}
