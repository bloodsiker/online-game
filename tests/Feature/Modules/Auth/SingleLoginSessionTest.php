<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Modules\Auth\Application\Listeners\ActivateLoginSession;
use App\Modules\Auth\Presentation\Http\Middleware\EnsureSingleLoginSession;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SingleLoginSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('remember_token')->nullable();
            $table->string('active_login_token')->nullable();
        });
        DB::table('users')->insert(['id' => 1]);
    }

    private function requestFor(User $user): Request
    {
        $request = Request::create('/session-test');
        $session = app('session')->driver();
        $session->flush();
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);
        $this->app->instance('request', $request);
        Auth::guard('web')->setUser($user);

        return $request;
    }

    public function test_new_login_invalidates_previous_token_and_remember_cookie(): void
    {
        $user = (new User)->forceFill(['id' => 1, 'password' => 'test-password']);
        $request = $this->requestFor($user);
        $listener = new ActivateLoginSession;
        $listener->handle(new Login('web', $user, true));
        $first = $request->session()->get(ActivateLoginSession::SESSION_KEY);
        $remember = $user->getRememberToken();
        $listener->handle(new Login('web', $user, true));
        $active = $request->session()->get(ActivateLoginSession::SESSION_KEY);
        $this->assertNotSame($first, $active);
        $this->assertNotSame($remember, $user->getRememberToken());
        $this->assertSame($active, DB::table('users')->value('active_login_token'));
        $this->assertStringContainsString('|'.$user->getRememberToken().'|', Cookie::queued(Auth::guard('web')->getRecallerName())->getValue());
        $middleware = new EnsureSingleLoginSession;
        $this->assertSame('allowed', $middleware->handle($request, fn () => response('allowed'))->getContent());
        $request->session()->put(ActivateLoginSession::SESSION_KEY, $first);
        $request->headers->set('Accept', 'application/json');
        $this->assertSame(401, $middleware->handle($request, fn () => response('should not run'))->getStatusCode());
        $this->assertSame($active, DB::table('users')->value('active_login_token'));
        $this->assertSame($user->getRememberToken(), DB::table('users')->value('remember_token'));
        $this->assertTrue(Auth::guest());
    }

    public function test_old_session_without_token_is_rejected(): void
    {
        $request = $this->requestFor((new User)->forceFill(['id' => 1]));
        $response = (new EnsureSingleLoginSession)->handle($request, fn () => response('should not run'));
        $this->assertStringContainsString('window.top.location.href', $response->getContent());
        $this->assertTrue(Auth::guest());
    }

    public function test_guard_login_activates_session_via_registered_listener(): void
    {
        $user = (new User)->forceFill(['id' => 1, 'password' => 'test-password', 'remember_token' => 'old-token']);
        $request = $this->requestFor($user);
        Auth::guard('web')->login($user, true);
        $this->assertNotEmpty($request->session()->get(ActivateLoginSession::SESSION_KEY));
        $this->assertSame($user->active_login_token, DB::table('users')->value('active_login_token'));
        $this->assertStringContainsString('|'.$user->getRememberToken().'|', Cookie::queued(Auth::guard('web')->getRecallerName())->getValue());
    }
}
