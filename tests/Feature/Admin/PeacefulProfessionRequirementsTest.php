<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SkillController;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PeacefulProfessionRequirementsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('skill_level_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('exp_required');
            $table->unsignedInteger('exp_diff');
            $table->unique(['skill_id', 'lvl']);
        });
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('exp');
            $table->unsignedInteger('exp_up');
            $table->unsignedInteger('exp_diff');
            $table->timestamps();
        });

        app('redirect')->setSession($this->app->make('session.store'));
    }

    public function test_admin_can_replace_the_full_peaceful_profession_scale(): void
    {
        $skill = Skill::create(['name' => 'Травник', 'type' => 'peaceful']);
        DB::table('player_skills')->insert([
            'player_id' => 1,
            'skill_id' => $skill->id,
            'lvl' => 50,
            'exp' => 60000,
            'exp_up' => 1,
            'exp_diff' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $thresholds = [];
        for ($level = 1; $level <= 300; $level++) {
            $thresholds[$level] = $level * 100;
        }

        (new SkillController)->updatePeacefulProfessionRequirements(
            Request::create('/admin/skill/'.$skill->id.'/peaceful-requirements', 'POST', ['requirements' => $thresholds]),
            $skill,
        );

        $this->assertSame(300, DB::table('skill_level_requirements')->where('skill_id', $skill->id)->count());
        $this->assertSame(5000, DB::table('skill_level_requirements')->where('skill_id', $skill->id)->where('lvl', 50)->value('exp_required'));
        $this->assertSame(100, DB::table('skill_level_requirements')->where('skill_id', $skill->id)->where('lvl', 50)->value('exp_diff'));
        $this->assertSame(5000, DB::table('player_skills')->where('skill_id', $skill->id)->value('exp_up'));
        $this->assertSame(100, DB::table('player_skills')->where('skill_id', $skill->id)->value('exp_diff'));
    }
}
