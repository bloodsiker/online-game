<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Дзеркальне відображення - створює клонів боса
 */
class MirrorImageMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $imageCount = $this->getConfig('image_count', 2);
        $imageHpPercent = $this->getConfig('image_hp_percent', 30);
        $imageDamagePercent = $this->getConfig('image_damage_percent', 50);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Створюємо дзеркальні відображення
        $images = [];
        for ($i = 1; $i <= $imageCount; $i++) {
            $images[] = [
                'id' => $i,
                'hp_percent' => $imageHpPercent,
                'damage_percent' => $imageDamagePercent,
                'alive' => true,
                'created_at_turn' => $context->getCurrentTurn(),
            ];
        }

        $metadata['mirror_images'] = $images;
        $metadata['mirror_images_active'] = true;

        $battle->boss_metadata = $metadata;
        $battle->save();

        $context->addLog(sprintf(
            '<p><b class="color-mirror">👯 %s создает %d зеркальных отражений!</b></p>',
            $context->getLocationMonster()->monster->name,
            $imageCount
        ));

        $context->addLog(sprintf(
            '<p class="color-info">Каждое отображение имеет %d%% HP и наносит %d%% ущерб оригиналу!</p>',
            $imageHpPercent,
            $imageDamagePercent
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $count = $this->getConfig('image_count', 2);

        return "Босс создал {$count} зеркальных отражений";
    }
}
