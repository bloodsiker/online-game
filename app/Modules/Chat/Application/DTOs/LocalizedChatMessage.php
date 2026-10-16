<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\DTOs;

use Illuminate\Database\Eloquent\Model;

final readonly class LocalizedChatMessage
{
    public function __construct(public string $key, public array $params = []) {}

    /** @return array{translations: array<string, string>} */
    public static function contentName(Model $model, string $field = 'name'): array
    {
        return ['translations' => array_intersect_key($model->getTranslations($field), array_flip(['ru', 'uk']))];
    }

    public function payload(): array
    {
        $encode = function (mixed $value) use (&$encode): mixed {
            if ($value instanceof self) {
                return $value->payload();
            }

            return is_array($value) ? array_map($encode, $value) : $value;
        };

        return ['key' => $this->key, 'params' => array_map($encode, $this->params)];
    }
}
