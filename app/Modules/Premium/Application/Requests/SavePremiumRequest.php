<?php

declare(strict_types=1);

namespace App\Modules\Premium\Application\Requests;

use App\Modules\Premium\Application\Services\ExchangeDiamonds;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class SavePremiumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Маршрут защищён AdminMiddleware.
    }

    public function rules(): array
    {
        $rules = [
            'gold_per_diamond' => ['required', 'integer', 'min:1', 'max:1000000'],
            'levels' => ['required', 'array:1,2,3,4,5', 'size:5'],
        ];
        foreach (range(1, 5) as $level) {
            $rules["levels.$level.threshold"] = ['required', 'integer', 'min:1', 'max:9999999'];
            $rules["levels.$level.bonuses"] = ['required', 'array:'.implode(',', array_column(PremiumBonus::cases(), 'value'))];
            $rules["levels.$level.bonus_enabled"] = ['sometimes', 'array:'.implode(',', array_column(PremiumBonus::cases(), 'value'))];
            foreach (PremiumBonus::cases() as $bonus) {
                $rules["levels.$level.bonus_enabled.$bonus->value"] = ['sometimes', 'boolean'];
                $rules["levels.$level.bonuses.$bonus->value"] = [
                    'required', $bonus->isBoolean() || $bonus === PremiumBonus::BAG_SLOTS ? 'integer' : 'numeric',
                    'min:0', 'max:'.$bonus->max(),
                ];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $previous = 0;
            foreach (range(1, 5) as $level) {
                $cents = ExchangeDiamonds::cents((string) $this->input("levels.$level.threshold"));
                if ($cents <= $previous) {
                    $validator->errors()->add("levels.$level.threshold", 'Пороги должны быть положительными и строго возрастать от уровня 1 к 5.');
                }
                $previous = $cents;
            }
        }];
    }
}
