<?php

use App\Modules\Clan\Domain\Enums\ClanPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $bit = ClanPermission::START_CLAN_BOSS->bit();

        DB::table('clan_roles')
            ->where('is_leader', true)
            ->get(['id', 'permissions'])
            ->each(fn ($role) => DB::table('clan_roles')->where('id', $role->id)->update([
                'permissions' => (int) $role->permissions | $bit,
            ]));
    }

    public function down(): void
    {
        $bit = ClanPermission::START_CLAN_BOSS->bit();

        DB::table('clan_roles')
            ->get(['id', 'permissions'])
            ->each(fn ($role) => DB::table('clan_roles')->where('id', $role->id)->update([
                'permissions' => (int) $role->permissions & ~$bit,
            ]));
    }
};
