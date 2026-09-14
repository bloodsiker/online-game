<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Clan;

use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClanLogServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('clan_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_writes_a_clan_log_from_models(): void
    {
        $clan = (new Clan)->forceFill(['id' => 15]);
        $clan->exists = true;
        $user = (new User)->forceFill(['id' => 27]);
        $user->exists = true;

        $log = app(ClanLogService::class)->write(
            $clan,
            $user,
            ClanLogAction::TAX_PAID,
            'Налог оплачен.',
        );

        $this->assertSame(15, $log->clan_id);
        $this->assertSame(27, $log->user_id);
        $this->assertSame(ClanLogAction::TAX_PAID, $log->action);
        $this->assertSame('Налог оплачен.', $log->details);
    }
}
