<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Modules\News\Infrastructure\Persistence\Models\LaunchSubscriber;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Presentation\Http\Middleware\ApplyGameLocale;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class LaunchSubscriptionListTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware(ApplyGameLocale::class);
        (require database_path('migrations/2026_11_06_000001_create_game_launch_subscribers_table.php'))->up();
    }

    public function test_guests_and_non_admin_players_cannot_read_subscriber_emails(): void
    {
        LaunchSubscriber::query()->create(['email' => 'private@example.test', 'locale' => 'ru']);
        $this->get(route('admin.launch-subscriptions.index'))->assertRedirect('/')->assertDontSee('private@example.test');
        $this->signIn(admin: false);
        $this->get(route('admin.launch-subscriptions.index'))->assertRedirect('/')->assertDontSee('private@example.test');
    }

    public function test_admin_sees_emails_languages_dates_and_notification_status_even_after_launch(): void
    {
        config(['game.prelaunch' => false]);
        $this->signIn();
        LaunchSubscriber::query()->create(['email' => 'pending@example.test', 'locale' => 'uk']);
        $sent = LaunchSubscriber::query()->create(['email' => 'sent@example.test', 'locale' => 'ru']);
        $sent->forceFill(['notified_at' => now()->subMinute()])->save();

        $this->get(route('admin.launch-subscriptions.index'))->assertOk()
            ->assertSee('Подписки на открытие')->assertSee('pending@example.test')->assertSee('sent@example.test')
            ->assertSee('Українська')->assertSee('Русский')->assertSee('Ожидает отправки')->assertSee('Отправлено')
            ->assertSee($sent->notified_at->format('d.m.Y H:i'))->assertSee('Найдено подписчиков:');
        $this->assertNull(LaunchSubscriber::query()->where('email', 'pending@example.test')->value('notified_at'));
    }

    public function test_filters_combine_email_language_and_delivery_status(): void
    {
        $this->signIn();
        foreach ([['match@example.test', 'uk', false], ['match.sent@example.test', 'uk', true],
            ['match.ru@example.test', 'ru', false], ['other@example.test', 'uk', false]] as [$email, $locale, $sent]) {
            $subscriber = LaunchSubscriber::query()->create(['email' => $email, 'locale' => $locale]);
            if ($sent) {
                $subscriber->forceFill(['notified_at' => now()])->save();
            }
        }
        $response = $this->get(route('admin.launch-subscriptions.index', ['q' => ' MATCH ', 'status' => 'pending', 'locale' => 'uk']))
            ->assertOk()->assertSee('match@example.test')->assertDontSee('match.sent@example.test')
            ->assertDontSee('match.ru@example.test')->assertDontSee('other@example.test');
        $this->assertSame(1, $response->viewData('subscribers')->total());

        $this->get(route('admin.launch-subscriptions.index', ['status' => 'sent']))
            ->assertOk()->assertSee('match.sent@example.test')->assertDontSee('match@example.test');
    }

    public function test_pagination_has_fifty_rows_newest_first_and_preserves_filters(): void
    {
        $this->signIn();
        foreach (range(1, 52) as $number) {
            LaunchSubscriber::query()->create(['email' => 'seeker'.$number.'@example.test', 'locale' => 'uk']);
        }
        $filters = ['q' => 'seeker', 'status' => 'pending', 'locale' => 'uk'];
        $response = $this->get(route('admin.launch-subscriptions.index', $filters))->assertOk();
        $paginator = $response->viewData('subscribers');
        $this->assertCount(50, $paginator->items());
        $this->assertSame(52, $paginator->total());
        $this->assertSame(52, $paginator->first()->id);
        $this->assertSame(3, $paginator->last()->id);
        foreach ($filters as $name => $value) {
            $this->assertStringContainsString($name.'='.$value, $paginator->nextPageUrl());
        }
        $response = $this->get(route('admin.launch-subscriptions.index', $filters + ['page' => 2]))->assertOk();
        $this->assertCount(2, $response->viewData('subscribers')->items());
        $this->assertSame(2, $response->viewData('subscribers')->first()->id);
    }

    public function test_invalid_filters_are_rejected_and_empty_state_renders(): void
    {
        $this->signIn();
        $this->get(route('admin.launch-subscriptions.index'))->assertOk()->assertSee('Подписки не найдены.');
        $this->getJson(route('admin.launch-subscriptions.index', ['status' => 'invalid', 'locale' => 'en', 'q' => ['bad']]))
            ->assertUnprocessable()->assertJsonValidationErrors(['q', 'status', 'locale']);
    }

    private function signIn(bool $admin = true): void
    {
        $user = (new User)->forceFill([
            'id' => 1, 'is_admin' => $admin, 'email_verified_at' => now(), 'active_login_token' => 'subscription-test',
        ])->setRelation('player', null);
        $this->actingAs($user)->withSession(['active_login_token' => 'subscription-test']);
    }
}
