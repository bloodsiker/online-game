<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClanIconRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('filesystems.disks.public.url', '/storage');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('news_1')->nullable();
            $table->text('news_2')->nullable();
            $table->text('news_3')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->integer('lvl')->default(1);
            $table->unsignedInteger('warehouse_capacity')->default(50);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedBigInteger('treasury')->default(0);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('clan_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
        });

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Clan Owner',
        ]);

        DB::table('clans')->insert([
            'id' => 1,
            'name' => 'Elders',
            'icon' => 'clan_icons/elders.gif',
            'owner_id' => 1,
        ]);

        $this->withoutMiddleware(AdminMiddleware::class);
    }

    public function test_clan_list_renders_public_storage_icon_url(): void
    {
        $response = $this->get(route('admin.clans'));

        $response->assertOk();
        $response->assertSee('src="/storage/clan_icons/elders.gif"', false);
        $response->assertDontSee('src="clan_icons/elders.gif"', false);
        $response->assertSee('aria-label="Навигационная цепочка"', false);
        $response->assertSee('<span>Кланы</span>', false);
    }
}
