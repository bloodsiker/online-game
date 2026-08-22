<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Post;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Post\Application\UseCases\BulkLetters;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BulkLettersBatchLoadingTest extends TestCase
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
            $table->timestamps();
        });
        Schema::create('post_letters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->unsignedBigInteger('recipient_user_id');
            $table->string('subject')->default('');
            $table->text('text')->nullable();
            $table->unsignedInteger('money')->default(0);
            $table->timestamp('money_claimed_at')->nullable();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedInteger('item_amount')->default(1);
            $table->timestamp('item_claimed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sender_deleted_at')->nullable();
            $table->timestamp('recipient_deleted_at')->nullable();
            $table->timestamps();
        });

        for ($id = 1; $id <= 5; $id++) {
            DB::table('share_items')->insert(['id' => $id, 'name' => 'Attachment '.$id]);
            DB::table('post_letters')->insert([
                'id' => $id,
                'recipient_user_id' => 10,
                'share_item_id' => $id,
                'item_amount' => 1,
            ]);
        }
    }

    public function test_share_items_are_eager_loaded_for_all_selected_letters(): void
    {
        $backpackService = Mockery::mock(BackpackService::class);
        $backpackService->shouldReceive('giveItemsByShareItem')->times(5)->andReturn([]);

        $user = (new User)->forceFill(['id' => 10]);
        $user->exists = true;

        $wasPreventingLazyLoading = Model::preventsLazyLoading();
        Model::preventLazyLoading(true);
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $affected = (new BulkLetters($backpackService))->execute($user, [1, 2, 3, 4, 5], 'claim');
        } finally {
            Model::preventLazyLoading($wasPreventingLazyLoading);
        }

        $shareItemSelects = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains(strtolower($query), 'share_items'));

        $this->assertSame(5, $affected);
        $this->assertCount(1, $shareItemSelects);
    }
}
