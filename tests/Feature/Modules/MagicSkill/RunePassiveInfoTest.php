<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\MagicSkill\Presentation\Http\RunePassiveInfoController;
use App\Modules\Share\Domain\Enums\CombatPassiveType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class RunePassiveInfoTest extends TestCase
{
    public function test_each_passive_opens_its_own_skill_info_with_actual_value(): void
    {
        foreach (CombatPassiveType::cases() as $type) {
            $url = route('magic_skill.rune_passive.info', ['type' => $type->value, 'value' => 15]);
            $this->assertStringContainsString('/magic-skill/info/rune/'.$type->value, $url);
            $html = (new RunePassiveInfoController)(Request::create($url), $type)->render();
            $this->assertStringContainsString($type->label(), $html);
            $this->assertStringContainsString(e($type->description(15)), $html);
            $this->assertStringContainsString($type->iconUrl(), $html);
            $this->assertStringNotContainsString('Уровень заклинания', $html);
        }
    }

    public function test_invalid_value_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new RunePassiveInfoController)(Request::create('/', 'GET', ['value' => '<script>']), CombatPassiveType::VAMPIRISM);
    }
}
