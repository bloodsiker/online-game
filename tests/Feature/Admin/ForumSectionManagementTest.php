<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForumSectionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('forum_sections', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_topics')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->timestamps();
        });

        Schema::create('forum_topics', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->timestamps();
        });

        $this->withoutMiddleware(AdminMiddleware::class);
    }

    public function test_admin_can_create_and_update_a_forum_category(): void
    {
        $this->post(route('admin.forum.sections.store'), [
            'name' => 'Игровой мир',
            'slug' => 'game-world',
            'description' => 'Обсуждение игрового мира.',
            'sort_order' => 20,
            'is_active' => '1',
            'allow_topics' => '1',
        ])->assertRedirect(route('admin.forum.sections.index'));

        $sectionId = (int) DB::table('forum_sections')->value('id');

        $this->put(route('admin.forum.sections.update', $sectionId), [
            'name' => 'Мир игры',
            'slug' => 'game-world',
            'sort_order' => 10,
        ])->assertRedirect(route('admin.forum.sections.index'));

        $this->assertDatabaseHas('forum_sections', [
            'id' => $sectionId,
            'name' => 'Мир игры',
            'sort_order' => 10,
            'is_active' => false,
            'allow_topics' => false,
            'allow_comments' => false,
        ]);
    }

    public function test_category_with_children_cannot_be_deleted_or_nested(): void
    {
        $rootId = DB::table('forum_sections')->insertGetId([
            'name' => 'Главная категория',
            'slug' => 'root',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $childId = DB::table('forum_sections')->insertGetId([
            'parent_id' => $rootId,
            'name' => 'Подраздел',
            'slug' => 'child',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('admin.forum.sections.destroy', $rootId))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('forum_sections', ['id' => $rootId]);

        $this->post(route('admin.forum.sections.store'), [
            'parent_id' => $childId,
            'name' => 'Третий уровень',
            'slug' => 'third-level',
            'is_active' => '1',
        ])->assertSessionHasErrors('parent_id');

        $this->delete(route('admin.forum.sections.destroy', $childId))
            ->assertRedirect(route('admin.forum.sections.index'));
        $this->assertDatabaseMissing('forum_sections', ['id' => $childId]);
    }
}
