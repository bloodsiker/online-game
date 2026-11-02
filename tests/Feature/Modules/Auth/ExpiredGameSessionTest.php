<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Modules\Auth\Presentation\Http\Middleware\EnsureEmailIsVerified;
use App\Modules\Auth\Presentation\Http\Middleware\EnsureSingleLoginSession;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ExpiredGameSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([EnsureSingleLoginSession::class, EnsureEmailIsVerified::class]);
        Route::middleware(['web', 'auth'])->get('/_test/session/protected', fn () => response('Protected'));
        Route::middleware('web')->get('/_test/session/error', fn () => abort(500));
        Route::middleware('web')->get('/_test/session/csrf', fn () => abort(419));
    }

    public function test_unauthenticated_frame_redirects_entire_window_without_visiting_post_only_login(): void
    {
        $this->get('/_test/session/protected')->assertUnauthorized()
            ->assertHeader('X-Game-Session-Expired', '1')
            ->assertSee('window.top.location.replace', false)
            ->assertDontSee('/login', false);
    }

    public function test_ajax_receives_unauthorized_status_and_home_destination(): void
    {
        $this->getJson('/_test/session/protected')->assertUnauthorized()
            ->assertJsonPath('redirect', route('index'));
    }

    public function test_session_status_is_available_to_guests_and_not_cached(): void
    {
        $response = $this->getJson(route('auth.session.status'));
        $response->assertOk()->assertJsonPath('authenticated', false);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_session_status_recognizes_authenticated_player(): void
    {
        $user = (new User)->forceFill(['id' => 1])->setRelation('player', null);
        $this->actingAs($user)->getJson(route('auth.session.status'))->assertOk()->assertJsonPath('authenticated', true);
    }

    public function test_method_error_contains_session_check_even_without_web_middleware_running(): void
    {
        $this->post('/_test/session/protected')->assertStatus(405)
            ->assertSee('game-session.js', false)->assertSee('data-check-session="1"', false);
    }

    public function test_server_and_csrf_errors_check_session_instead_of_blindly_redirecting(): void
    {
        foreach (['error' => 500, 'csrf' => 419] as $path => $status) {
            $this->get('/_test/session/'.$path)->assertStatus($status)->assertSee('data-check-session="1"', false);
        }
    }

    public function test_real_server_error_keeps_its_status_for_authenticated_player(): void
    {
        $user = (new User)->forceFill(['id' => 1])->setRelation('player', null);
        $this->actingAs($user)->getJson('/_test/session/error')->assertStatus(500)
            ->assertHeaderMissing('X-Game-Session-Expired');
    }
}
