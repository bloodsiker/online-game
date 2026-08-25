<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final review, IMPORTANT 10: syncMagicSkillBook() звался только для предметов
 * типа BOOK, поэтому смена типа книги на любой другой оставляла осиротевшую
 * строку magic_skill_books — заклинание навсегда «занято» (unique magic_skill_id
 * не даёт привязать его к настоящей книге).
 */
class ItemBookLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('resource');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('rarity')->default('common');
            $table->string('slot')->nullable();
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->integer('count_use')->default(0);
            $table->integer('expire')->nullable();
            $table->boolean('is_two_hand')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_auction_sellable')->default(false);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_weight')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('skill_lvl')->nullable();
            $table->integer('skill_exp')->nullable();
            $table->string('upgrade_scroll_type')->nullable();
            $table->json('gem_stats')->nullable();
            $table->string('rune_rarity')->nullable();
            $table->json('rune_stat_pool')->nullable();
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
            $table->boolean('is_passive')->default(false);
            $table->timestamps();
        });
        Schema::create('magic_skill_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('magic_skill_id')->unique();
            $table->timestamps();
        });

        // Контроллер редиректит с ->with('success'|'error'), а редиректору вне
        // HTTP-стека сессию никто не проставляет — делаем это сами.
        app('redirect')->setSession($this->app->make('session.store'));
    }

    /** @param  array<string, string>  $overrides */
    private function saveRequest(array $overrides): Request
    {
        return Request::create('/admin/item', 'POST', [
            'name' => 'Книга: Огненная искра',
            'type' => ShareItemType::BOOK->value,
            'rarity' => 'common',
            ...$overrides,
        ]);
    }

    public function test_retyping_a_book_away_from_book_clears_the_magic_skill_link(): void
    {
        $skill = MagicSkill::create(['name' => 'Огненная искра', 'slug' => 'fire_spark']);
        $item = new ShareItem;
        $item->name = 'Книга: Огненная искра';
        $item->type = ShareItemType::BOOK;
        $item->save();
        MagicSkillBook::create(['share_item_id' => $item->id, 'magic_skill_id' => $skill->id]);

        (new ItemController)->info(
            $this->saveRequest(['type' => ShareItemType::MISC->value, 'name' => 'Просто хлам']),
            $item,
        );

        $this->assertSame(ShareItemType::MISC, $item->fresh()->type);
        $this->assertSame(
            0,
            MagicSkillBook::where('share_item_id', $item->id)->count(),
            'смена типа с BOOK обязана снимать привязку к заклинанию',
        );

        // Заклинание снова свободно — его можно привязать к настоящей книге.
        $realBook = new ShareItem;
        $realBook->name = 'Книга: Огненная искра (настоящая)';
        $realBook->type = ShareItemType::BOOK;
        $realBook->save();

        MagicSkillBook::create(['share_item_id' => $realBook->id, 'magic_skill_id' => $skill->id]);
        $this->assertSame(1, MagicSkillBook::where('magic_skill_id', $skill->id)->count());
    }

    public function test_link_is_dropped_even_when_the_form_still_submits_a_magic_skill_id(): void
    {
        // Форма админки шлёт select с заклинанием независимо от выбранного типа,
        // поэтому одного лишь «magic_skill_id пуст → удалить» недостаточно.
        $skill = MagicSkill::create(['name' => 'Огненная искра', 'slug' => 'fire_spark']);
        $item = new ShareItem;
        $item->name = 'Книга: Огненная искра';
        $item->type = ShareItemType::BOOK;
        $item->save();
        MagicSkillBook::create(['share_item_id' => $item->id, 'magic_skill_id' => $skill->id]);

        (new ItemController)->info(
            $this->saveRequest([
                'type' => ShareItemType::MISC->value,
                'magic_skill_id' => (string) $skill->id,
            ]),
            $item,
        );

        $this->assertSame(
            0,
            MagicSkillBook::where('share_item_id', $item->id)->count(),
            'тип предмета авторитетнее присланного magic_skill_id',
        );
    }

    public function test_a_book_saved_as_a_book_keeps_its_link(): void
    {
        $skill = MagicSkill::create(['name' => 'Огненная искра', 'slug' => 'fire_spark']);
        $item = new ShareItem;
        $item->name = 'Книга: Огненная искра';
        $item->type = ShareItemType::BOOK;
        $item->save();
        MagicSkillBook::create(['share_item_id' => $item->id, 'magic_skill_id' => $skill->id]);

        (new ItemController)->info(
            $this->saveRequest(['magic_skill_id' => (string) $skill->id]),
            $item,
        );

        $this->assertSame(
            $skill->id,
            MagicSkillBook::where('share_item_id', $item->id)->value('magic_skill_id'),
            'сохранение книги книгой не должно ломать существующую привязку',
        );
    }
}
