<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Post;

use App\Modules\Post\Application\Services\AdministrationMailService;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesPostFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class PostMysqlConcurrencyTest extends TestCase
{
    use CreatesPostFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('POST_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_post_race_*.');
        }
        // These checks precede every schema or fixture mutation.
        $this->assertMatchesRegularExpression('/^codex_post_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        Event::fake([MailboxUnreadStateUpdated::class]);
        if (! Schema::hasTable('users')) {
            $this->createPostTables();
        }
        DB::table('post_letters')->whereNotNull('administration_thread_id')->delete();
        foreach (['post_letters', 'backpacks', 'items', 'share_items', 'users'] as $table) {
            DB::table($table)->delete();
        }
        $this->seedPostFixture();
    }

    private function race(array $first, ?array $second = null): array
    {
        return $this->runConcurrentRequests('tests/Support/post-race-worker.php', $this->database, 'POST_RACE_TEST_DATABASE', $first, $second);
    }

    private function letter(int $money = 50, int $items = 2): PostLetter
    {
        return PostLetter::create([
            'sender_user_id' => 1, 'recipient_user_id' => 3, 'subject' => 'Reward', 'text' => 'Test',
            'money' => $money, 'share_item_id' => $items > 0 ? 1 : null, 'item_amount' => $items,
        ]);
    }

    public function test_parallel_claims_deliver_real_inventory_and_money_once(): void
    {
        $letter = $this->letter();
        $results = $this->race(['claim', 3, json_encode([$letter->id]), 'claim']);
        $this->assertSame(1, array_sum(array_column($results, 'affected')));
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 50]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('backpacks', ['user_id' => 3, 'count' => 2]);
        $this->assertNotNull($letter->fresh()->money_claimed_at);
        $this->assertNotNull($letter->fresh()->item_claimed_at);
    }

    public function test_overlapping_batches_claim_each_attachment_once_despite_reversed_selection(): void
    {
        $first = $this->letter();
        $second = $this->letter();
        $results = $this->race(['claim', 3, json_encode([$first->id, $second->id]), 'claim'], ['claim', 3, json_encode([$second->id, $first->id]), 'claim']);
        $this->assertSame(2, array_sum(array_column($results, 'affected')));
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 100]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('backpacks', ['user_id' => 3, 'count' => 4]);
    }

    public function test_parallel_sends_cannot_overdraw_the_sender(): void
    {
        $results = $this->race(['send', 1, 90, 'Recipient']);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 9]);
        $this->assertDatabaseCount('post_letters', 1);
    }

    public function test_two_senders_cannot_overfill_the_same_inbox(): void
    {
        for ($i = 0; $i < 39; $i++) {
            $this->letter(0, 0);
        }
        $results = $this->race(['send', 1, 0, 'Recipient'], ['send', 2, 0, 'Recipient']);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseCount('post_letters', 40);
        $this->assertSame(199, (int) DB::table('users')->whereIn('id', [1, 2])->sum('money'));
    }

    public function test_reciprocal_sends_finish_without_deadlock(): void
    {
        $results = $this->race(['send', 1, 10, 'Other'], ['send', 2, 10, 'Sender']);
        $this->assertSame(2, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 89]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 89]);
        $this->assertDatabaseCount('post_letters', 2);
    }

    public function test_administration_reply_and_player_deletion_use_the_same_lock_order(): void
    {
        $thread = app(AdministrationMailService::class)->send(User::without('player')->findOrFail(3), 'Help', 'Question');
        $this->race(['admin_reply', 1, $thread->id, 'Answer'], ['delete', 3, $thread->id, '']);
        $this->assertNotNull($thread->fresh()->sender_deleted_at);
        $this->assertSame('answered', $thread->fresh()->administration_status);
        $this->assertDatabaseHas('post_letters', ['administration_thread_id' => $thread->id, 'recipient_user_id' => 3, 'text' => 'Answer']);
    }

    public function test_claim_and_delete_are_serialized_without_partial_delivery(): void
    {
        $letter = $this->letter();
        $this->race(['claim', 3, json_encode([$letter->id]), 'claim'], ['delete', 3, $letter->id, '']);
        $letter->refresh();
        $this->assertNotNull($letter->recipient_deleted_at);
        $claimed = $letter->money_claimed_at !== null;
        $this->assertSame($claimed, $letter->item_claimed_at !== null);
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => $claimed ? 50 : 0]);
        $this->assertSame($claimed ? 2 : 0, (int) DB::table('backpacks')->sum('count'));
    }
}
