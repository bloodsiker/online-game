<?php

declare(strict_types=1);

namespace Tests\Unit\Frontend;

use App\Modules\Backpack\Domain\DTO\BackpackDTO;
use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRenderer;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Location\Application\DTOs\LocationMoveDirectionDTO;
use App\Modules\Location\Application\DTOs\LocationPageDTO;
use App\Modules\Location\Application\DTOs\LocationPlayerFrameDTO;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LocalizedGameplayTest extends TestCase
{
    public function test_catalogs_have_identical_keys_and_preserve_printf_parameters(): void
    {
        $ru = json_decode(file_get_contents(lang_path('ru.json')), true, flags: JSON_THROW_ON_ERROR);
        $uk = json_decode(file_get_contents(lang_path('uk.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(array_keys($ru), array_keys($uk));
        foreach ($ru as $key => $value) {
            preg_match_all('/%(?:\d+\$)?(?:\.\d+)?[sdf%]/', $value, $source);
            preg_match_all('/%(?:\d+\$)?(?:\.\d+)?[sdf%]/', $uk[$key], $translated);
            $this->assertSame($source[0], $translated[0], $key);
        }
    }

    public function test_same_persisted_battle_message_renders_for_each_viewer(): void
    {
        app()->setLocale('uk');
        $template = '<p>Вы ударили %s оружием %s! <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>';
        $stored = BattleLogTranslator::format($template, 'Глиняный Голем', 'Начальный меч', 15, 4);
        $this->assertStringContainsString('Вы ударили', $stored);
        $this->assertStringNotContainsString('Ви вдарили', $stored);
        $html = BattleLogTranslator::render($stored);
        $this->assertStringContainsString('Ви вдарили Глиняный Голем зброєю Начальный меч', $html);
        $this->assertStringContainsString('Пошкодження: <b>15</b> (ваш досвід +4)', $html);
        $this->assertStringNotContainsString('battle-i18n', $html);
        app()->setLocale('ru');
        $this->assertSame(sprintf($template, 'Глиняный Голем', 'Начальный меч', 15, 4), BattleLogTranslator::render($stored));
        $this->assertSame('<p>Старый лог</p>', BattleLogTranslator::render('<p>Старый лог</p>'));
    }

    public function test_nested_battle_messages_translate_without_changing_damage_or_experience(): void
    {
        $duration = BattleLogTranslator::format('%d ход(а)', 3);
        $log = BattleLogTranslator::format('<p class="color-debuff">%s На вас наложен эффект: <b>%s</b> (%s)</p>', '☠', 'Подземный яд', $duration);
        app()->setLocale('uk');
        $html = BattleLogTranslator::render($log);
        $this->assertStringContainsString('На вас накладено ефект', $html);
        $this->assertStringContainsString('Подземный яд', $html);
        $this->assertStringContainsString('3 хід(-и)', $html);
        $this->assertStringNotContainsString('battle-i18n', $html);
    }

    public function test_tooltip_labels_translate_but_item_names_remain_unchanged(): void
    {
        app()->setLocale('uk');
        $this->assertSame('Спритність', ShareItemStatType::AGILITY->label());
        $this->assertSame('Зброя', ShareItemType::WEAPON->label());
        $this->assertSame('Рукавиці', ShareItemSlot::GLOVES->label());
        $this->assertSame('Повернення втраченого при смерті досвіду', ItemEffectType::RESTORE_LOST_EXP->label());
        $item = new ShareItem(['type' => 'weapon', 'slot' => 'hand', 'is_two_hand' => true]);
        $item->setRelation('stats', collect());
        $item->setRelation('effects', collect());
        $item->setRelation('useLimit', null);
        $rows = ItemTooltipStatsBuilder::buildForTooltip($item);
        $this->assertContains(['title' => 'Тип', 'value' => 'Дворучне'], $rows);
        $tooltip = new ItemTooltipDto(
            id: 1, title: 'Начальный меч', color: '#000', image: '/item.png', kind: 'Зброя',
            price: '', diamond: '', lev: [], skills: [], desc: '', store: false,
            nogive: true, nosell: true, noweight: true,
        );
        $data = $tooltip->toArray();
        $this->assertSame('Начальный меч', $data['title']);
        $this->assertSame('Предмет не можна передати!', $data['nogive']);
        $this->assertSame('Предмет не можна здати у скупку', $data['nosell']);
        $script = (new ItemTooltipRenderer)->render([$data]);
        $this->assertStringContainsString('window.gameTranslations', $script);
        $this->assertStringContainsString('window.itemTooltip', $script);
    }

    public function test_attack_button_translates_and_keeps_its_hotkey_contract(): void
    {
        app()->setLocale('uk');
        $player = new class
        {
            public int $hp_now = 50;

            public int $mp_now = 20;

            public function hasEquippedMagicSkill(): bool
            {
                return false;
            }
        };
        $decorator = new class
        {
            public function getHpMax(): int
            {
                return 100;
            }

            public function getMpMax(): int
            {
                return 40;
            }
        };
        $html = view('battle::partials.action_panel', [
            'player' => $player, 'playerDecorator' => $decorator,
            'battle' => (object) ['id' => 10],
            'randomAttackedMonster' => (object) ['locationMonster' => (object) ['id' => 20]],
        ])->render();
        $this->assertStringContainsString('Атакувати зброєю (Q)', $html);
        $this->assertStringContainsString('Ваш хід', $html);
        $this->assertStringContainsString('id="weapon-attack" data-q-repeat="true"', $html);
        $this->assertStringContainsString('window.availableMoves = []', $html);
    }

    public function test_all_affected_blade_templates_compile_to_valid_php(): void
    {
        $roots = [
            'app/Modules/Backpack/Presentation/Views', 'app/Modules/Battle/Presentation/Views',
            'app/Modules/Location/Presentation/Views', 'app/Modules/Item/Presentation/Views',
        ];
        $paths = [resource_path('views/components/game/loot-panel.blade.php'), base_path('app/Modules/Interface/Presentation/Views/index.blade.php')];
        foreach ($roots as $root) {
            foreach (File::allFiles(base_path($root)) as $file) {
                if (str_ends_with($file->getFilename(), '.blade.php')) {
                    $paths[] = $file->getPathname();
                }
            }
        }
        foreach ($paths as $path) {
            $process = new Process([PHP_BINARY, '-l']);
            $process->setInput(app('blade.compiler')->compileString(file_get_contents($path)));
            $process->run();
            $this->assertTrue($process->isSuccessful(), $path.': '.$process->getErrorOutput().$process->getOutput());
        }
    }

    public function test_backpack_tabs_and_context_actions_render_in_ukrainian(): void
    {
        app()->setLocale('uk');
        $user = new class
        {
            public int $money = 10;

            public int $diamond = 0;

            public function getBagCount(): int
            {
                return 30;
            }
        };
        $html = view('backpack::bag', [
            'user' => $user,
            'data' => new BackpackDTO(backpack: new Collection, items: new Collection, countItems: 0, group: 'main'),
            'itemTooltipScript' => '',
        ])->render();
        $this->assertStringContainsString('<html lang="uk">', $html);
        $this->assertStringContainsString('>Речі</a>', $html);
        $this->assertStringContainsString('>Подарунки</a>', $html);
        $this->assertStringContainsString('>Використати</div>', $html);
        $this->assertStringContainsString('>Надіслати в чат</div>', $html);
    }

    public function test_location_labels_translate_without_translating_content(): void
    {
        app()->setLocale('uk');
        $moves = [];
        foreach (['up' => 'вверх', 'down' => 'вниз', 'north' => 'север', 'south' => 'юг', 'east' => 'восток', 'west' => 'запад'] as $key => $label) {
            $moves[$key] = new LocationMoveDirectionDTO(available: false, url: null, label: $label);
        }
        $page = new LocationPageDTO(
            locationId: 1, name: 'Город Нейрин', description: 'Описание из базы', image: null,
            dungeonSession: null, hasBattle: true, battleUrl: '/fight/1', monsters: [], npcs: [], moves: $moves,
            itemsOnLocationCount: 0, takeItemsUrl: '/items', gatheringUrl: null, structures: [], gateActions: [],
            locationUsersJson: '[]', playerFrame: new LocationPlayerFrameDTO(50, 100, 20, 40, 0, 1), hasClanMembership: false,
        );
        $html = view('location::index', ['page' => $page])->render();
        $this->assertStringContainsString('Вас атаковано!', $html);
        $this->assertStringContainsString('>Продовжити</a>', $html);
        $this->assertStringContainsString('Город Нейрин', $html);
        $page = new LocationPageDTO(...[...get_object_vars($page), 'hasBattle' => false]);
        $html = view('location::index', ['page' => $page])->render();
        $this->assertStringContainsString('північ', $html);
        $this->assertStringContainsString('Шукати тут', $html);
    }

    public function test_translating_loot_buttons_does_not_change_the_open_chest_branch(): void
    {
        app()->setLocale('uk');
        $item = (object) [
            'name' => 'Сундук из базы', 'image' => '/chest.png', 'count' => 1, 'actionLabel' => 'Открыть',
            'actionUrl' => '/open/1', 'lockpickingUrl' => null,
        ];
        $html = Blade::render('<x-game.loot-panel :items="$items" :count="1" location-url="/location" />', ['items' => [$item]]);
        $this->assertStringContainsString('Сундук из базы', $html);
        $this->assertStringContainsString('method="post" action="/open/1"', $html);
        $this->assertStringContainsString('value="Відкрити"', $html);
        $this->assertStringContainsString('Скриню можна відкрити й оглянути', $html);
    }
}
