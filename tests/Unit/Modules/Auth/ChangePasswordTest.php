<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth;

use App\Modules\Auth\Application\Services\ChangePasswordService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ChangePasswordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Тест разрешён только в SQLite :memory:.');
        }

        config(['cache.default' => 'array', 'session.driver' => 'array']);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    private function user(): User
    {
        DB::table('users')->insert([
            'id' => 1,
            'password' => Hash::make('old-password-123'),
            'remember_token' => 'old-token',
        ]);

        return User::query()->without('player')->findOrFail(1);
    }

    public function test_password_change_hashes_password_and_rotates_remember_token(): void
    {
        $user = $this->user();
        $response = $this->actingAs($user)->withSession(['_token' => 'test-csrf-token'])->postJson(route('settings.password.update'), [
            '_token' => 'test-csrf-token',
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $stored = DB::table('users')->first();
        $this->assertTrue(Hash::check('new-password-456', $stored->password));
        $this->assertFalse(Hash::check('old-password-123', $stored->password));
        $this->assertNotSame('old-token', $stored->remember_token);
        $this->assertSame($stored->password, session('password_hash_web'));
        $this->assertSame('test-csrf-token', session('_token'));
    }

    public function test_invalid_current_password_or_confirmation_leaves_password_unchanged(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        foreach ([
            ['current' => 'wrong-password', 'confirmation' => 'new-password-456', 'field' => 'current_password'],
            ['current' => 'old-password-123', 'confirmation' => 'different-password', 'field' => 'password'],
        ] as $case) {
            $this->withSession(['_token' => 'test-csrf-token'])->postJson(route('settings.password.update'), [
                '_token' => 'test-csrf-token',
                'current_password' => $case['current'],
                'password' => 'new-password-456',
                'password_confirmation' => $case['confirmation'],
            ])->assertUnprocessable()->assertJsonValidationErrors($case['field']);
        }
        $this->assertSame($user->password, DB::table('users')->value('password'));
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->withSession(['_token' => 'test-csrf-token'])->postJson(route('settings.password.update'), ['_token' => 'test-csrf-token'])->assertUnauthorized();
    }

    public function test_request_without_csrf_cannot_change_password(): void
    {
        $user = $this->user();
        $this->actingAs($user)->postJson(route('settings.password.update'), [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertStatus(419);
        $this->assertSame($user->password, DB::table('users')->value('password'));
    }

    public function test_short_same_or_overlong_unicode_password_is_rejected(): void
    {
        $user = $this->user();
        foreach (['short', 'old-password-123', str_repeat('я', 40)] as $password) {
            $this->actingAs($user)->withSession(['_token' => 'test-csrf-token'])->postJson(route('settings.password.update'), [
                '_token' => 'test-csrf-token',
                'current_password' => 'old-password-123',
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->assertSame($user->password, DB::table('users')->value('password'));
    }

    public function test_service_rechecks_password_when_database_value_has_changed(): void
    {
        $user = $this->user();
        $hash = Hash::make('changed-in-another-request');
        DB::table('users')->where('id', 1)->update(['password' => $hash]);
        try {
            app(ChangePasswordService::class)->execute(
                user: $user,
                currentPassword: 'old-password-123',
                newPassword: 'new-password-456',
            );
            $this->fail('Изменение со старым паролем должно быть запрещено.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('current_password', $exception->errors());
        }
        $this->assertSame($hash, DB::table('users')->value('password'));
    }
}
