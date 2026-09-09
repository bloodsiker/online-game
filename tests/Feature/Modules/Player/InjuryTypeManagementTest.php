<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Application\Requests\Admin\SaveInjuryTypeRequest;
use App\Modules\Player\Application\UseCases\Admin\DeleteInjuryType;
use App\Modules\Player\Application\UseCases\Admin\SaveInjuryType;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InjuryTypeManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('injury_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('body_part');
            $table->unsignedTinyInteger('severity');
            $table->string('image')->nullable();
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('drop_weight')->default(1);
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('player_injuries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('injury_type_id')->nullable();
            $table->string('body_part');
            $table->unsignedTinyInteger('severity');
            $table->timestamp('applied_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function test_admin_can_configure_an_injury_and_remove_its_image(): void
    {
        Storage::fake('public');
        $save = new SaveInjuryType;

        $injuryType = $save->execute(
            new InjuryType,
            $this->data(),
            UploadedFile::fake()->create('head-wound.png', 20, 'image/png'),
            false,
        );

        $storedImage = $injuryType->getRawOriginal('image');
        $this->assertNotNull($storedImage);
        Storage::disk('public')->assertExists($storedImage);
        $this->assertSame(1800, $injuryType->duration_seconds);
        $this->assertSame([
            ['stat' => 'intuition', 'value' => -12.5, 'is_percent' => true],
        ], $injuryType->stat_modifiers);

        $save->execute($injuryType, $this->data(), null, true);

        $this->assertNull($injuryType->fresh()->getRawOriginal('image'));
        Storage::disk('public')->assertMissing($storedImage);
    }

    public function test_injury_used_by_a_player_can_only_be_disabled_not_deleted(): void
    {
        $injuryType = (new SaveInjuryType)->execute(new InjuryType, $this->data(), null, false);
        DB::table('player_injuries')->insert([
            'player_id' => 10,
            'injury_type_id' => $injuryType->id,
            'body_part' => 'head',
            'severity' => 2,
            'applied_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse((new DeleteInjuryType)->execute($injuryType));
        $this->assertDatabaseHas('injury_types', ['id' => $injuryType->id]);
    }

    public function test_seeded_hand_slug_with_underscore_passes_admin_validation(): void
    {
        $injuryType = (new SaveInjuryType)->execute(new InjuryType, [
            ...$this->data(),
            'slug' => 'light-left_hand',
        ], null, false);
        $request = SaveInjuryTypeRequest::create('/admin/injury/'.$injuryType->id, 'POST', [
            ...$this->data(),
            'slug' => 'light-left_hand',
            'delete_image' => false,
        ]);
        $route = new Route('POST', 'admin/injury/{injuryType}', []);
        $route->bind($request);
        $route->setParameter('injuryType', $injuryType);
        $request->setRouteResolver(static fn (): Route => $route);
        $request->setContainer(app())->setRedirector(app('redirect'));

        $request->validateResolved();

        $this->assertSame('light-left_hand', $request->validated('slug'));
    }

    private function data(): array
    {
        return [
            'name' => 'Рваная рана головы',
            'slug' => 'head-laceration',
            'description' => 'Тестовая травма',
            'body_part' => 'head',
            'severity' => 2,
            'duration_minutes' => 30,
            'drop_weight' => 25,
            'stat_modifiers' => [
                ['stat' => 'intuition', 'value' => -12.5, 'is_percent' => true],
            ],
            'is_active' => true,
        ];
    }
}
