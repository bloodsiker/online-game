<?php

namespace Tests\Unit\Frontend;

use Tests\TestCase;

class MonsterDropFrequencyTest extends TestCase
{
    public function test_drop_frequency_is_rendered_for_percentage_chances(): void
    {
        foreach ([
            [50, '1/2'],
            [1, '1/100'],
            [100, '1/1'],
            [0.001, '1/100000'],
            [30, '1/3.33'],
            [0, 'Не выпадает'],
        ] as [$chance, $expected]) {
            $html = view('admin.monster._drop_frequency', ['chance' => $chance])->render();
            $this->assertStringContainsString('>'.$expected.'</span>', $html);
        }
    }
}
