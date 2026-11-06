<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Post;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Post\Application\UseCases\BulkLetters;
use App\Modules\Post\Application\UseCases\DeleteLetter;
use App\Modules\Post\Application\UseCases\GetMailbox;
use App\Modules\Post\Application\UseCases\ReadLetter;
use App\Modules\Post\Application\UseCases\SendLetter;
use App\Modules\Post\Application\UseCases\SendSystemLetter;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\Post\Domain\Exceptions\ClaimAttachmentsBlocked;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesPostFixture;
use Tests\TestCase;

class PostTransactionsTest extends TestCase
{
    use CreatesPostFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createPostTables();
        $this->seedPostFixture();
        Event::fake([MailboxUnreadStateUpdated::class]);
        $this->freezeTime();
    }

    private function user(int $id = 3): User
    {
        return User::without('player')->findOrFail($id);
    }

    private function letter(int $money = 50, int $items = 2): PostLetter
    {
        return PostLetter::create([
            'sender_user_id' => 1, 'recipient_user_id' => 3, 'subject' => 'Награда', 'text' => 'Текст',
            'money' => $money, 'share_item_id' => $items > 0 ? 1 : null, 'item_amount' => max(1, $items),
        ]);
    }

    public function test_repeated_claim_with_duplicate_ids_delivers_money_and_items_once(): void
    {
        $letter = $this->letter();
        $stale = $this->user();
        $this->assertSame(1, app(BulkLetters::class)->execute($stale, [$letter->id, $letter->id], 'claim'));
        $this->assertSame(0, app(BulkLetters::class)->execute($stale, [$letter->id], 'claim'));
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 50]);
        $this->assertDatabaseCount('items', 1);
        $this->assertDatabaseHas('backpacks', ['user_id' => 3, 'count' => 2]);
        $this->assertNotNull($letter->fresh()->money_claimed_at);
        $this->assertNotNull($letter->fresh()->item_claimed_at);
    }

    public function test_sender_and_other_players_cannot_claim_and_sender_deletion_does_not_hide_recipient_copy(): void
    {
        $letter = $this->letter();
        $this->assertSame(0, app(BulkLetters::class)->execute($this->user(1), [$letter->id], 'claim'));
        $this->assertSame(0, app(BulkLetters::class)->execute($this->user(2), [$letter->id], 'claim_delete'));
        $this->assertTrue(app(DeleteLetter::class)->execute($this->user(1), $letter->id));
        $this->assertNull($letter->fresh()->recipient_deleted_at);
        $this->assertSame(1, app(BulkLetters::class)->execute($this->user(), [$letter->id], 'claim'));
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 50]);
    }

    public function test_claim_and_delete_is_atomic_and_repeating_it_cannot_claim_again(): void
    {
        $letter = $this->letter();
        $this->assertSame(1, app(BulkLetters::class)->execute($this->user(), [$letter->id], 'claim_delete'));
        $this->assertSame(0, app(BulkLetters::class)->execute($this->user(), [$letter->id], 'claim_delete'));
        $this->assertNotNull($letter->fresh()->recipient_deleted_at);
        $this->assertDatabaseHas('backpacks', ['user_id' => 3, 'count' => 2]);
        Event::assertDispatched(MailboxUnreadStateUpdated::class, fn ($event) => $event->userId === 3 && ! $event->hasUnread);
    }

    public function test_deleted_letter_cannot_be_claimed_or_read_even_by_its_recipient(): void
    {
        $letter = $this->letter();
        $this->assertTrue(app(DeleteLetter::class)->execute($this->user(), $letter->id));
        $this->assertSame(0, app(BulkLetters::class)->execute($this->user(), [$letter->id], 'claim'));
        $this->assertNull(app(ReadLetter::class)->execute($this->user(), $letter->id));
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 0]);
        $this->assertDatabaseCount('backpacks', 0);
    }

    public function test_failed_second_attachment_rolls_back_the_entire_batch_including_real_inventory_writes(): void
    {
        $first = $this->letter(10, 1);
        $second = $this->letter(20, 1);
        $backpack = Mockery::mock(BackpackService::class, [app(ItemRequirementService::class)])->makePartial();
        $backpack->shouldReceive('giveItemsByShareItem')->once()->ordered()->passthru();
        $backpack->shouldReceive('giveItemsByShareItem')->once()->ordered()->andThrow(new RuntimeException('delivery unavailable'));
        $this->app->instance(BackpackService::class, $backpack);
        try {
            app(BulkLetters::class)->execute($this->user(), [$first->id, $second->id], 'claim_delete');
            $this->fail('Delivery must roll back.');
        } catch (RuntimeException $exception) {
            $this->assertSame('delivery unavailable', $exception->getMessage());
            $this->assertDatabaseHas('users', ['id' => 3, 'money' => 0]);
            $this->assertDatabaseCount('items', 0);
            $this->assertDatabaseCount('backpacks', 0);
            foreach ([$first, $second] as $letter) {
                $this->assertNull($letter->fresh()->money_claimed_at);
                $this->assertNull($letter->fresh()->item_claimed_at);
                $this->assertNull($letter->fresh()->recipient_deleted_at);
            }
            Event::assertNotDispatched(MailboxUnreadStateUpdated::class);
        }
    }

    public function test_wallet_overflow_rolls_back_preceding_claims_and_preserves_all_attachments(): void
    {
        DB::table('users')->where('id', 3)->update(['money' => WalletBalanceLimits::MAX_BALANCE - 1]);
        $first = $this->letter(1, 1);
        $second = $this->letter(2, 1);
        try {
            app(BulkLetters::class)->execute($this->user(), [$first->id, $second->id], 'claim_delete');
            $this->fail('Wallet overflow must be rejected.');
        } catch (ClaimAttachmentsBlocked) {
            $this->assertDatabaseHas('users', ['id' => 3, 'money' => WalletBalanceLimits::MAX_BALANCE - 1]);
            $this->assertDatabaseCount('backpacks', 0);
            $this->assertNull($first->fresh()->money_claimed_at);
            $this->assertNull($second->fresh()->recipient_deleted_at);
        }
    }

    public function test_send_rechecks_stale_sender_balance_and_spends_postage_only_for_successful_letters(): void
    {
        $stale = $this->user(1);
        $this->assertTrue(app(SendLetter::class)->execute($stale, 'Recipient', 'Тема', 'Текст', 90)->ok);
        $this->assertFalse(app(SendLetter::class)->execute($stale, 'Recipient', 'Тема', 'Текст', 90)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 9]);
        $this->assertDatabaseHas('users', ['id' => 3, 'money' => 0]);
        $this->assertDatabaseCount('post_letters', 1);
        $this->assertDatabaseHas('post_letters', ['money' => 90, 'money_claimed_at' => null]);
    }

    public function test_full_mailbox_does_not_charge_sender_and_system_letters_still_bypass_capacity(): void
    {
        for ($index = 0; $index < GetMailbox::CAPACITY - 1; $index++) {
            $this->letter(0, 0);
        }
        $this->assertTrue(app(SendLetter::class)->execute($this->user(1), 'Recipient', 'Тема', 'Текст', 0)->ok);
        $this->assertFalse(app(SendLetter::class)->execute($this->user(2), 'Recipient', 'Тема', 'Текст', 0)->ok);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 100]);
        $this->assertDatabaseCount('post_letters', GetMailbox::CAPACITY);
        $this->assertTrue(app(SendSystemLetter::class)->execute('Recipient', 'Система', 'Текст', 0, null, 1, false)->ok);
        $this->assertDatabaseCount('post_letters', GetMailbox::CAPACITY + 1);
    }

    public function test_invalid_amounts_or_recipients_do_not_create_letters_or_spend_money(): void
    {
        $send = app(SendLetter::class);
        $this->assertFalse($send->execute($this->user(1), 'Recipient', 'Тема', 'Текст', -1)->ok);
        $this->assertFalse($send->execute($this->user(1), 'Recipient', 'Тема', 'Текст', PHP_INT_MAX)->ok);
        $this->assertFalse($send->execute($this->user(1), 'Sender', 'Тема', 'Текст', 0)->ok);
        $this->assertFalse($send->execute($this->user(1), 'missing', 'Тема', 'Текст', 0)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseCount('post_letters', 0);
    }

    public function test_expired_mailbox_pruning_prevents_later_claiming_of_removed_letters(): void
    {
        $letter = $this->letter();
        $letter->forceFill(['created_at' => now()->subDays(GetMailbox::UNREAD_TTL_DAYS + 1)])->save();
        $this->assertCount(0, app(GetMailbox::class)->inbox($this->user()));
        $this->assertSame(0, app(BulkLetters::class)->execute($this->user(), [$letter->id], 'claim'));
        $this->assertDatabaseCount('backpacks', 0);
    }

    public function test_claim_and_delete_routes_are_post_only_and_letter_buttons_submit_csrf_forms(): void
    {
        foreach (['post.claim', 'post.delete'] as $route) {
            $this->assertSame(['POST'], Route::getRoutes()->getByName($route)->methods());
        }
        Auth::setUser($this->user());
        $letter = $this->letter();
        $letter->subject = 'O\'Brien "награда"';
        $html = view('post::partials.letter', ['letter' => $letter])->render();
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(2, $xpath->query('//form[@method="post"]/input[@name="_token"]')->length);
        $this->assertStringContainsString('confirm(', $xpath->query('//form[@onsubmit]')->item(0)->getAttribute('onsubmit'));
    }

    public function test_outer_transaction_rollback_does_not_emit_arrival_or_charge_postage(): void
    {
        try {
            DB::transaction(function (): void {
                $this->assertTrue(app(SendLetter::class)->execute($this->user(1), 'Recipient', 'Тема', 'Текст', 10)->ok);
                throw new RuntimeException('outer failure');
            });
        } catch (RuntimeException) {
            $this->assertDatabaseCount('post_letters', 0);
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
            Event::assertNotDispatched(MailboxUnreadStateUpdated::class);
        }
    }
}
