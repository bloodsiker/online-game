<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Domain\Services\MessageRenderer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MessageRendererTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('resource');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('rarity')->default('common');
            $table->timestamps();
        });
    }

    public function test_it_renders_share_item_shortcode(): void
    {
        DB::connection('sqlite')->table('share_items')->insert([
            'id' => 42,
            'type' => 'quest',
            'name' => 'Квестовый ключ',
            'description' => 'Открывает дверь',
            'rarity' => 'uncommon',
        ]);

        $html = app(MessageRenderer::class)->render('Найден [[share_item_42]]', true);

        $this->assertStringContainsString('Квестовый ключ', $html);
        $this->assertStringContainsString('color:#339900', $html);
        $this->assertStringContainsString(route('items.info.share', ['id' => 42]), $html);
        $this->assertStringNotContainsString('[[share_item_42]]', $html);
    }

    public function test_it_marks_unknown_share_item_shortcode(): void
    {
        $html = app(MessageRenderer::class)->render('[[share_item_999]]', true);

        $this->assertStringContainsString('[???]', $html);
        $this->assertStringContainsString('Предмет не найден', $html);
    }
}
