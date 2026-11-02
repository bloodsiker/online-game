<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Requests;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveTreasureMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'share_item_id' => ['required', 'integer', Rule::exists('share_items', 'id')->where('type', ShareItemType::TREASURE_MAP->value), Rule::unique('treasure_map_configs')->ignore($this->route('config'))],
            'reward_share_item_id' => ['required', 'integer', Rule::exists('share_items', 'id')->where('type', ShareItemType::CHEST->value)],
            'reputation_id' => ['required', 'integer', 'exists:reputations,id'],
            'required_reputation' => ['required', 'integer', 'min:0', 'max:100000000'],
            'reputation_reward' => ['required', 'integer', 'min:1', 'max:100000'],
            'search_duration_seconds' => ['required', 'integer', 'min:1', 'max:3600'],
            'success_chance_percent' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:100'],
            'clue_ru' => ['required', 'string', 'max:5000'], 'clue_uk' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $item = ShareItem::with('lockConfig')->find($this->integer('reward_share_item_id'));
            if ((int) $item?->lockConfig?->lock_required_skill <= 0) {
                $validator->errors()->add('reward_share_item_id', 'У шкатулки должен быть настроен замок: требуемый навык взлома больше нуля.');
            }
        }];
    }
}
