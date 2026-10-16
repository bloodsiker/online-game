<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Interface;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Mappers\HeroPageViewMapper;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Interface\Domain\Events\PlayerStateUpdated;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class PlayerEffectStateBroadcasterTest extends TestCase
{
    public function test_it_broadcasts_current_effects_to_player_channel(): void
    {
        Carbon::setTestNow('2026-09-18 12:00:00');
        Event::fake([PlayerStateUpdated::class]);

        $effect = new Effect([
            'name' => 'Проклятие молчания',
            'slug' => 'chat_mute',
            'type' => 'debuff',
            'image' => '/main/images/gag.gif',
            'description' => 'Запрещает писать в чат.',
        ]);
        $activeEffect = (new PlayerActiveEffect)->forceFill([
            'id' => 21,
            'player_id' => 7,
            'applied_at' => Carbon::parse('2026-09-18 12:00:00'),
            'expires_at' => Carbon::parse('2026-09-18 12:30:00'),
        ]);
        $activeEffect->setRelation('effect', $effect);

        $repository = $this->createMock(InterfaceReadRepository::class);
        $repository->expects(self::once())
            ->method('getPlayerActiveEffects')
            ->with(7)
            ->willReturn(new Collection([$activeEffect]));

        $player = (new Player)->forceFill(['id' => 7]);
        (new PlayerEffectStateBroadcaster($repository, new HeroPageViewMapper))->broadcast($player);

        Event::assertDispatched(
            PlayerStateUpdated::class,
            fn (PlayerStateUpdated $event): bool => $event->playerId === 7
                && $event->state['effects'][0]['name'] === 'Проклятие молчания'
                && $event->state['effects'][0]['duration'] === 1800
                && $event->state['effects'][0]['is_curse'] === true,
        );

        Carbon::setTestNow();
    }

    public function test_background_broadcast_uses_recipient_language_not_worker_locale(): void
    {
        app()->setLocale('ru');
        request()->attributes->set('game_settings.10', ['language' => 'uk']);
        Event::fake([PlayerStateUpdated::class]);
        $effect = new Effect(['name' => ['ru' => 'Ожог', 'uk' => 'Опік'], 'description' => ['ru' => 'Урон', 'uk' => 'Шкода'], 'slug' => 'burn']);
        $active = (new PlayerActiveEffect)->forceFill(['id' => 21, 'player_id' => 7, 'applied_at' => now(), 'expires_at' => now()->addMinute()]);
        $active->setRelation('effect', $effect);
        $repository = $this->createMock(InterfaceReadRepository::class);
        $repository->method('getPlayerActiveEffects')->willReturn(new Collection([$active]));
        $player = (new Player)->forceFill(['id' => 7, 'user_id' => 10]);
        (new PlayerEffectStateBroadcaster($repository, new HeroPageViewMapper))->broadcast($player);
        Event::assertDispatched(PlayerStateUpdated::class, fn (PlayerStateUpdated $event): bool => $event->state['effects'][0]['name'] === 'Опік'
            && $event->state['effects'][0]['description'] === 'Шкода');
        $this->assertSame('ru', app()->getLocale());
    }
}
