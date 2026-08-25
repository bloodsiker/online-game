<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Effect\Application\DTOs\PlayerEffectNotificationDTO;
use PHPUnit\Framework\TestCase;

class AttackResultDTOTest extends TestCase
{
    public function test_merge_preserves_player_effect_notifications(): void
    {
        $notification = new PlayerEffectNotificationDTO(
            id: 'monster_poison_15',
            name: 'Отравление',
            duration: 6,
            isCurse: true,
        );
        $source = (new AttackResultDTO)->notifyPlayerEffect($notification);

        $result = (new AttackResultDTO)->merge($source);

        $this->assertSame([$notification], $result->getPlayerEffects());
        $this->assertSame([
            'id' => 'monster_poison_15',
            'name' => 'Отравление',
            'duration' => 6,
            'is_curse' => true,
        ], $notification->jsonSerialize());
    }

    public function test_same_effect_notification_is_replaced_with_latest_duration(): void
    {
        $result = (new AttackResultDTO)
            ->notifyPlayerEffect(new PlayerEffectNotificationDTO('poison_4', 'Яд', 3, true))
            ->notifyPlayerEffect(new PlayerEffectNotificationDTO('poison_4', 'Сильный яд', 8, true));

        $this->assertCount(1, $result->getPlayerEffects());
        $this->assertSame('Сильный яд', $result->getPlayerEffects()[0]->name);
        $this->assertSame(8, $result->getPlayerEffects()[0]->duration);
    }
}
