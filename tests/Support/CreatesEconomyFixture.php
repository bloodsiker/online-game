<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesEconomyFixture
{
    private function createEconomyTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('money')->default(100);
            $table->integer('diamond')->default(100);
            $table->timestamps();
        });
        Schema::create('locations', fn (Blueprint $table) => $table->id());
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type')->default('resource');
            $table->integer('price')->default(20);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_stackable')->default(true);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            foreach (['upgrade_lvl', 'upgrade_pity', 'upgrade_fail_streak', 'socket_count', 'rune_slot_count', 'additional_attack', 'count_use', 'is_open'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->integer('count')->default(1);
            $table->integer('equipped')->default(0);
            $table->timestamps();
        });
        Schema::create('shop_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('structure_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('price')->default(20);
            $table->integer('diamond')->default(0);
            $table->timestamps();
        });
        Schema::create('shop_carts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_item_id');
            $table->integer('quantity');
            $table->timestamps();
        });
        Schema::create('shop_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shop_item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('quantity');
            $table->timestamps();
        });
        foreach ([
            '2025_06_04_195655_create_clan_table.php',
            '2026_09_10_000002_add_tax_period_to_clans_table.php',
            '2026_08_22_120000_create_item_action_logs_table.php',
            '2026_09_25_000005_create_shop_purchase_logs_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function seedEconomyFixture(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Leader', 'money' => 100, 'diamond' => 100],
            ['id' => 2, 'name' => 'Member', 'money' => 100, 'diamond' => 100],
            ['id' => 3, 'name' => 'Outsider', 'money' => 100, 'diamond' => 100],
        ]);
        foreach ([1 => 'clan_bank', 2 => 'shop', 3 => 'premium_shop'] as $id => $type) {
            DB::table('structures')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Структура '.$id]), 'type' => $type]);
        }
        DB::table('clans')->insert(['id' => 1, 'owner_id' => 1, 'name' => 'Test Clan', 'treasury' => 100, 'tax_paid_until' => now()->addDay()]);
        DB::table('clan_roles')->insert([
            ['id' => 1, 'clan_id' => 1, 'name' => 'Leader', 'is_leader' => true, 'permissions' => ClanPermission::allBits()],
            ['id' => 2, 'clan_id' => 1, 'name' => 'Member', 'is_leader' => false, 'permissions' => ClanPermission::WITHDRAW_MONEY->bit()],
        ]);
        DB::table('clan_members')->insert([
            ['id' => 1, 'clan_id' => 1, 'user_id' => 1, 'role_id' => 1],
            ['id' => 2, 'clan_id' => 1, 'user_id' => 2, 'role_id' => 2],
        ]);
        DB::table('share_items')->insert(['id' => 1, 'name' => json_encode(['ru' => 'Агат', 'uk' => 'Агат'])]);
        DB::table('items')->insert(['id' => 1, 'share_item_id' => 1]);
        DB::table('backpacks')->insert(['id' => 1, 'user_id' => 1, 'item_id' => 1, 'count' => 10]);
        DB::table('shop_items')->insert([
            ['id' => 1, 'structure_id' => 2, 'share_item_id' => 1, 'price' => 20, 'diamond' => 0],
            ['id' => 2, 'structure_id' => 3, 'share_item_id' => 1, 'price' => 0, 'diamond' => 20],
        ]);
    }

    private function economyUser(int $id = 1): User
    {
        return User::without('player')->findOrFail($id);
    }

    private function sellSelection(int $quantity = 10): array
    {
        return [1 => ['selected' => 1, 'count' => $quantity]];
    }

    private function seedEconomyCart(bool $premium = false, int $quantity = 2): void
    {
        DB::table('shop_carts')->insert(['user_id' => 1, 'shop_item_id' => $premium ? 2 : 1, 'quantity' => $quantity]);
    }
}
