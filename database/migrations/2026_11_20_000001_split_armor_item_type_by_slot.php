<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Тип «armor» был общим для всех доспехов. Теперь у каждого слота свой тип
 * (helmet, shoulder, forearm, legging, chain_armor, cloak, shoes, gloves), «armor» — только нагрудная броня.
 */
return new class extends Migration
{
    private const SLOTS = ['helmet', 'shoulder', 'forearm', 'legging', 'chain_armor', 'cloak', 'shoes', 'gloves'];

    public function up(): void
    {
        foreach (self::SLOTS as $slot) {
            DB::table('share_items')->where('type', 'armor')->where('slot', $slot)->update(['type' => $slot]);
        }
    }

    public function down(): void
    {
        DB::table('share_items')->whereIn('type', self::SLOTS)->update(['type' => 'armor']);
    }
};
