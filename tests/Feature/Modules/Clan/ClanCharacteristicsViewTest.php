<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Clan\Domain\Models\Clan;
use Tests\TestCase;

final class ClanCharacteristicsViewTest extends TestCase
{
    public function test_level_progress_uses_the_large_reputation_bar_and_keeps_experience_values(): void
    {
        $html = view('clan::characteristics', [
            'clan' => (new Clan)->forceFill(['name' => 'Клан', 'lvl' => 2, 'experience' => 1250]),
            'nextLevel' => (object) ['level' => 3, 'experience_required' => 2000],
            'currentLevelExperience' => 1000,
            'experienceToNextLevel' => 750,
            'progressPercent' => 25,
        ])->render();

        $this->assertStringContainsString('class="rep-progress-bar__fill" style="width: 25%;"', $html);
        $this->assertStringContainsString('class="rep-progress-bar__text">250.00/1 000.00</div>', $html);
        $this->assertStringContainsString('progress-bar-1-border.png', $html);
        $this->assertStringNotContainsString('class="progress-bar"', $html);
    }

    public function test_maximum_level_keeps_the_existing_message_instead_of_a_progress_bar(): void
    {
        $html = view('clan::characteristics', [
            'clan' => (new Clan)->forceFill(['name' => 'Клан', 'lvl' => 10, 'experience' => 100000]),
            'nextLevel' => null,
        ])->render();

        $this->assertStringContainsString(__('Достигнут максимальный уровень'), $html);
        $this->assertStringNotContainsString('class="rep-progress-bar"', $html);
    }
}
