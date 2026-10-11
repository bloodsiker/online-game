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
        config(['cache.default' => 'array']);
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        DB::connection('sqlite')->table('users')->insert(['id' => 17, 'name' => 'BlooDSikeR']);

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

    public function test_mention_recipient_is_clickable_and_preserves_space_and_text(): void
    {
        $html = app(MessageRenderer::class)->render('to[BlooDSikeR] хай');
        $this->assertStringContainsString('<span class="chat-to">» <a', $html);
        $this->assertStringContainsString('data-name="BlooDSikeR"', $html);
        $this->assertStringContainsString('chatPlayerClick(Number(this.dataset.uid || 0), this.dataset.name); return false;', $html);
        $this->assertStringContainsString('class="chat-to-recipient n player-link" data-uid="17"', $html);
        $this->assertStringContainsString('>BlooDSikeR</a></span> хай', $html);
    }

    public function test_mention_nickname_is_escaped_in_text_and_attribute(): void
    {
        $html = app(MessageRenderer::class)->render('to[O\'Brian"<x>] Привет');
        $this->assertStringContainsString('data-name="O&#039;Brian&quot;&lt;x&gt;"', $html);
        $this->assertStringNotContainsString('<x>', $html);
    }
}
