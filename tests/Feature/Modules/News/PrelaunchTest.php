<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\News;

use App\Modules\News\Application\Mail\LaunchAnnouncement;
use App\Modules\News\Application\UseCases\SendLaunchAnnouncements;
use App\Modules\News\Infrastructure\Persistence\Models\LaunchSubscriber;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class PrelaunchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array', 'cache.default' => 'array', 'game.prelaunch' => true]);
        DB::purge('sqlite');
        app()->setLocale('ru');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->withoutVite();
        (require database_path('migrations/2026_11_06_000001_create_game_launch_subscribers_table.php'))->up();
        Mail::fake();
    }

    public function test_guest_can_subscribe_with_normalized_email_and_current_language_without_creating_account(): void
    {
        $this->withSession(['language' => 'uk'])
            ->postJson(route('homepage.launch-subscription'), ['email' => '  Seeker@Example.test  '])
            ->assertOk()->assertJsonPath('ok', true)
            ->assertJsonPath('message', __('prelaunch.subscribed', locale: 'uk'));

        $subscriber = LaunchSubscriber::query()->sole();
        $this->assertSame('seeker@example.test', $subscriber->email);
        $this->assertSame('uk', $subscriber->locale);
        $this->assertNull($subscriber->notified_at);
        $this->assertFalse(auth()->check());
        Mail::assertNothingSent();
    }

    public function test_repeated_email_is_idempotent_and_does_not_reset_notification_state(): void
    {
        $subscriber = LaunchSubscriber::query()->create(['email' => 'seeker@example.test', 'locale' => 'ru']);
        $subscriber->forceFill(['notified_at' => now()])->save();
        $this->withSession(['language' => 'uk'])
            ->postJson(route('homepage.launch-subscription'), ['email' => 'Seeker@example.test'])->assertOk();
        $this->assertSame(1, LaunchSubscriber::query()->count());
        $this->assertNotNull($subscriber->fresh()->notified_at);
        $this->assertSame('ru', $subscriber->fresh()->locale);
    }

    public function test_invalid_email_is_rejected_and_subscription_endpoint_is_disabled_after_launch(): void
    {
        foreach (['invalid', '', ['bad@example.test']] as $email) {
            $this->postJson(route('homepage.launch-subscription'), ['email' => $email])
                ->assertUnprocessable()->assertJsonValidationErrors('email');
        }
        config(['game.prelaunch' => false]);
        $this->postJson(route('homepage.launch-subscription'), ['email' => 'seeker@example.test'])->assertNotFound();
        $this->assertSame(0, LaunchSubscriber::query()->count());
    }

    public function test_subscription_requests_are_rate_limited(): void
    {
        foreach (range(1, 6) as $attempt) {
            $this->postJson(route('homepage.launch-subscription'), ['email' => 'seeker@example.test'])->assertOk();
        }
        $this->postJson(route('homepage.launch-subscription'), ['email' => 'another@example.test'])->assertStatus(429);
        $this->assertSame(1, LaunchSubscriber::query()->count());
    }

    public function test_homepage_buttons_open_signup_dialog_and_never_send_password_in_get_parameters(): void
    {
        $html = $this->homepage();
        $this->assertSame(3, substr_count($html, ' data-prelaunch-open'));
        $this->assertStringContainsString('id="prelaunch-modal"', $html);
        $this->assertStringContainsString('data-prelaunch-login', $html);
        $this->assertStringContainsString('css/prelaunch.css', $html);
        $this->assertStringContainsString('js/prelaunch.js', $html);
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame('get', $xpath->evaluate('string(//form[@data-prelaunch-login]/@method)'));
        $this->assertSame('', $xpath->evaluate('string(//input[@id="userPassword"]/@name)'));
        $this->assertSame('', $xpath->evaluate('string(//input[@id="userEmail"]/@name)'));

        config(['game.prelaunch' => false]);
        $html = $this->homepage();
        $this->assertStringNotContainsString('id="prelaunch-modal"', $html);
        $this->assertStringNotContainsString('data-prelaunch-open', $html);
        $this->assertStringContainsString('action="'.route('login').'" method="post"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('href="'.route('register').'"', $html);
    }

    public function test_dialog_is_localized_in_ukrainian(): void
    {
        app()->setLocale('uk');
        $this->assertStringContainsString('Відкриття вже близько', $this->homepage());
        $this->assertStringContainsString('Залиште email', $this->homepage());
    }

    public function test_announcement_requires_explicit_send_and_open_game_and_is_not_repeated(): void
    {
        $ru = LaunchSubscriber::query()->create(['email' => 'ru@example.test', 'locale' => 'ru']);
        $uk = LaunchSubscriber::query()->create(['email' => 'uk@example.test', 'locale' => 'uk']);
        $this->artisan('game:announce-launch')->assertSuccessful();
        $this->artisan('game:announce-launch', ['--send' => true])->assertFailed();
        Mail::assertNothingSent();
        $this->assertNull($ru->fresh()->notified_at);

        config(['game.prelaunch' => false]);
        $this->artisan('game:announce-launch', ['--send' => true])->assertSuccessful();
        Mail::assertSentCount(2);
        Mail::assertSent(LaunchAnnouncement::class, fn ($mail): bool => $mail->hasTo('uk@example.test') && $mail->locale === 'uk');
        $this->assertNotNull($ru->fresh()->notified_at);
        $this->assertNotNull($uk->fresh()->notified_at);
        $this->assertSame(0, app(SendLaunchAnnouncements::class)->pendingCount());
        $this->artisan('game:announce-launch', ['--send' => true])->assertSuccessful();
        Mail::assertSentCount(2);
    }

    public function test_announcement_email_uses_game_design_and_subscriber_language(): void
    {
        foreach (['ru' => 'Мир «Вечности» ждёт вас', 'uk' => 'Світ «Вічності» чекає на вас'] as $locale => $heading) {
            app()->setLocale($locale);
            $html = (new LaunchAnnouncement)->render();
            $this->assertStringContainsString($heading, $html);
            $this->assertStringContainsString('news_bg2.gif', $html);
            $this->assertStringContainsString(route('register'), $html);
        }
    }

    private function homepage(): string
    {
        view()->share('errors', new ViewErrorBag);

        return view('news::index', [
            'selectedNews' => null, 'newsPosts' => collect(), 'topPlayers' => collect(),
            'topClans' => collect(), 'hotForumTopics' => collect(), 'onlineCount' => 0,
        ])->render();
    }
}
