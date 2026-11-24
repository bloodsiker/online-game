<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Share;

use App\Modules\Share\Domain\Enums\PassiveBonusStatType;
use Tests\TestCase;

class PassiveBonusStatTypePresentTest extends TestCase
{
    public function test_every_passive_stat_has_a_player_label_without_admin_hints(): void
    {
        foreach (PassiveBonusStatType::cases() as $type) {
            $label = $type->label();
            $this->assertNotSame('', $label);
            $this->assertStringNotContainsString('is_percent', $label);
            $this->assertStringNotContainsString('StatSheet', $label);
        }
    }

    public function test_percent_bonus_is_shown_with_label_and_percent_sign(): void
    {
        $this->assertSame(
            ['label' => 'Магическое сопротивление', 'value' => '+5%'],
            PassiveBonusStatType::present(['type' => 'magic_resistance', 'value' => 5, 'is_percent' => true]),
        );
        $this->assertSame(
            ['label' => 'Шанс выжить при смертельном ударе', 'value' => '+5%'],
            PassiveBonusStatType::present(['type' => 'lethal_survive_chance', 'value' => 5, 'is_percent' => true]),
        );
    }

    public function test_magic_damage_percent_is_always_a_percent_even_without_flag(): void
    {
        $this->assertSame(
            ['label' => 'Магический урон', 'value' => '+3%'],
            PassiveBonusStatType::present(['type' => 'magic_damage_percent', 'value' => 3, 'is_percent' => false]),
        );
    }

    public function test_flat_bonus_has_no_percent_and_unknown_type_falls_back_to_key(): void
    {
        $this->assertSame('+4', PassiveBonusStatType::present(['type' => 'strength', 'value' => 4])['value']);
        $this->assertSame('mystery', PassiveBonusStatType::present(['type' => 'mystery', 'value' => 1])['label']);
    }

    public function test_admin_label_keeps_technical_hints_for_the_admin_form_only(): void
    {
        $this->assertStringContainsString('StatSheet', PassiveBonusStatType::LETHAL_SURVIVE_CHANCE->adminLabel());
        $this->assertSame('Сила', PassiveBonusStatType::STRENGTH->adminLabel());
    }
}
