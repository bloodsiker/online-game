<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Clan;

use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanRole;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClanTaxStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_tax_is_paid_while_paid_until_is_in_the_future(): void
    {
        Carbon::setTestNow('2026-09-10 12:00:00');

        $clan = new Clan(['tax_paid_until' => '2026-09-10 12:00:01']);

        $this->assertTrue($clan->hasPaidTax());
    }

    public function test_tax_is_overdue_at_the_paid_until_moment(): void
    {
        Carbon::setTestNow('2026-09-10 12:00:00');

        $clan = new Clan(['tax_paid_until' => '2026-09-10 12:00:00']);

        $this->assertFalse($clan->hasPaidTax());
    }

    public function test_missing_paid_until_means_overdue_tax(): void
    {
        $this->assertFalse((new Clan)->hasPaidTax());
    }

    public function test_pay_tax_permission_has_its_own_bit(): void
    {
        $role = new ClanRole(['permissions' => ClanPermission::PAY_TAX->bit()]);

        $this->assertTrue($role->hasPermission(ClanPermission::PAY_TAX));
        $this->assertFalse($role->hasPermission(ClanPermission::WITHDRAW_MONEY));
        $this->assertNotSame(0, ClanPermission::allBits() & ClanPermission::PAY_TAX->bit());
    }
}
