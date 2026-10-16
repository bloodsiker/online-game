<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Http\Controllers\Admin\StructureController;
use App\Modules\Location\Application\Mappers\LocationPageViewMapper;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Presentation\Http\Middleware\EnsureStructureActionIsActive;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StructureActionActiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['structures', 'share_actions'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('alias')->nullable();
                $table->string('type')->nullable();
                $table->integer('location_id')->nullable();
                $table->timestamps();
            });
        }
        Schema::create('structure_actions', function (Blueprint $table): void {
            $table->id();
            $table->integer('structure_id');
            $table->integer('share_action_id');
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_10_12_120000_add_is_active_to_structure_actions.php');
        $migration->up();
    }

    public function test_defaults_and_admin_switch_are_per_structure(): void
    {
        $first = Structure::query()->forceCreate(['name' => 'Первая', 'type' => 'shop']);
        $second = Structure::query()->forceCreate(['name' => 'Вторая', 'type' => 'shop']);
        $first->setRelation('influenceShopSections', new Collection);
        $second->setRelation('influenceShopSections', new Collection);
        $action = ShareAction::query()->forceCreate(['name' => 'Купить', 'alias' => 'buy']);
        $first->actions()->attach($action->id);
        $second->actions()->attach($action->id);
        $this->assertEquals(1, $first->actions()->first()->pivot->is_active);
        app(StructureController::class)->updateAction(Request::create('/', 'POST', ['is_active' => '0']), $first, $action);
        $this->assertEquals(0, $first->actions()->first()->pivot->is_active);
        $this->assertEquals(1, $second->actions()->first()->pivot->is_active);
        $location = new Location;
        $location->setRelation('structures', new Collection([$first->load('actions')]));
        $user = new User;
        $user->setRelation('clanMembership', null);
        $method = new \ReflectionMethod(LocationPageViewMapper::class, 'mapStructures');
        $cards = $method->invoke(app(LocationPageViewMapper::class), $location, $user);
        $this->assertCount(0, $cards[0]->actions);
        app(StructureController::class)->updateAction(Request::create('/', 'POST', ['is_active' => '1']), $first, $action);
        $location->setRelation('structures', new Collection([$first->load('actions')]));
        $this->assertCount(1, $method->invoke(app(LocationPageViewMapper::class), $location, $user)[0]->actions);
    }

    public function test_disabled_action_blocks_direct_purchase_but_not_other_structure(): void
    {
        $first = Structure::query()->forceCreate(['name' => 'Первая', 'type' => 'shop']);
        $second = Structure::query()->forceCreate(['name' => 'Вторая', 'type' => 'shop']);
        $action = ShareAction::query()->forceCreate(['name' => 'Купить', 'alias' => 'buy']);
        $first->actions()->attach($action->id, ['is_active' => false]);
        $second->actions()->attach($action->id);
        $middleware = new EnsureStructureActionIsActive;
        foreach ([$second->id, $first->id] as $id) {
            $request = Request::create('/shop/'.$id.'/purchase', 'POST');
            $route = (new Route('POST', '/shop/{id}/purchase', fn () => null))->name('shop.purchase');
            $route->bind($request);
            $request->setRouteResolver(fn () => $route);
            if ($id === $first->id) {
                $this->expectException(HttpException::class);
                $this->expectExceptionCode(0);
            }
            $response = $middleware->handle($request, fn () => new Response('allowed'));
            $this->assertSame('allowed', $response->getContent());
        }
    }

    public function test_unattached_action_cannot_be_updated_in_another_structure(): void
    {
        $structure = Structure::query()->forceCreate(['name' => 'Магазин', 'type' => 'shop']);
        $action = ShareAction::query()->forceCreate(['name' => 'Продать', 'alias' => 'sell']);
        $this->expectException(HttpException::class);
        app(StructureController::class)->updateAction(Request::create('/', 'POST', ['is_active' => '0']), $structure, $action);
    }

    public function test_admin_status_select_is_a_post_form_for_the_structure_action(): void
    {
        $structure = Structure::query()->forceCreate(['name' => 'Магазин', 'type' => 'shop']);
        $action = ShareAction::query()->forceCreate(['name' => 'Продать', 'alias' => 'sell']);
        $structure->actions()->attach($action->id, ['is_active' => false]);
        $html = view('admin.structures.partials.action-status', ['structure' => $structure, 'action' => $structure->actions()->first()])->render();
        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="is_active"', $html);
        $this->assertStringContainsString('value="0" selected', $html);
        $this->assertStringContainsString('name="_token"', $html);
    }
}
