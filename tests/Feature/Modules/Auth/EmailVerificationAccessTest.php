<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Tests\TestCase;

final class EmailVerificationAccessTest extends TestCase
{
    public function test_unverified_user_is_redirected_from_game_to_verification_notice(): void
    {
        $user = (new User)->forceFill([
            'id' => 456,
            'name' => 'Unverified Player',
            'email' => 'unverified@example.test',
            'email_verified_at' => null,
            'active_login_token' => 'test-login-token',
        ]);

        $this->actingAs($user)
            ->withSession(['active_login_token' => 'test-login-token'])
            ->get(route('game'))
            ->assertRedirect(route('verification.notice'));
    }
}
