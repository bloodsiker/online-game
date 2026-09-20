<?php

declare(strict_types=1);

use App\Modules\Moderation\Application\Services\ChatMuteEffectService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $effectId = DB::table('effects')
            ->where('slug', ChatMuteEffectService::EFFECT_SLUG)
            ->value('id');

        if ($effectId === null) {
            return;
        }

        DB::table('user_communication_mutes as mute')
            ->join('users as user', 'user.id', '=', 'mute.user_id')
            ->where('mute.scope', 'chat')
            ->whereNull('mute.revoked_at')
            ->where('mute.expires_at', '>', now())
            ->whereNotNull('user.player_id')
            ->orderBy('mute.expires_at')
            ->select(['user.player_id', 'mute.starts_at', 'mute.expires_at'])
            ->each(function (object $mute) use ($effectId): void {
                DB::table('player_active_effects')->updateOrInsert(
                    [
                        'player_id' => $mute->player_id,
                        'effect_id' => $effectId,
                        'battle_id' => null,
                    ],
                    [
                        'type' => null,
                        'source_player_id' => null,
                        'source_magic_skill_id' => null,
                        'applied_at' => $mute->starts_at,
                        'last_tick_at' => $mute->starts_at,
                        'next_tick_at' => null,
                        'expires_at' => $mute->expires_at,
                        'stacks' => 0,
                        'current_value' => null,
                        'tick_remainder' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        $effectId = DB::table('effects')
            ->where('slug', ChatMuteEffectService::EFFECT_SLUG)
            ->value('id');

        if ($effectId !== null) {
            DB::table('player_active_effects')->where('effect_id', $effectId)->delete();
        }
    }
};
