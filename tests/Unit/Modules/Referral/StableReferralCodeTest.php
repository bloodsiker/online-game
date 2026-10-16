<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Referral;

use App\Modules\Referral\Application\Mappers\ReferralPageViewMapper;
use App\Modules\Referral\Application\UseCases\ApplyReferralCode;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

final class StableReferralCodeTest extends TestCase
{
    public function test_referral_link_uses_stable_user_code_instead_of_nickname(): void
    {
        $user = new User;
        $user->id = 123;
        $user->name = 'RenamedPlayer';

        $page = (new ReferralPageViewMapper)->map(
            user: $user,
            invitedBy: null,
            referrals: new Collection,
            stages: new Collection,
        );

        $this->assertStringEndsWith('?ref=U123', $page->referralLink);
        $this->assertStringNotContainsString($user->name, $page->referralLink);
    }

    public function test_registration_resolves_referral_code_independently_of_nickname(): void
    {
        $repository = Mockery::mock(ReferralRepository::class);
        $referrer = new User;
        $referrer->id = 123;
        $referred = new User;
        $referred->id = 456;

        $repository->shouldReceive('findUserByReferralCode')->once()->with('U123')->andReturn($referrer);
        $repository->shouldReceive('hasReferralForReferredUser')->once()->with(456)->andReturnFalse();
        $repository->shouldReceive('createReferral')->once()->with(123, 456);

        (new ApplyReferralCode($repository))->handle($referred, 'U123');
    }
}
