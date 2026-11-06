<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesPostFixture
{
    private function createPostTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->integer('money')->default(0);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type')->default('resource');
            $table->boolean('is_stackable')->default(true);
            $table->integer('count_use')->default(0);
            $table->timestamps();
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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count')->default(1);
            $table->integer('equipped')->default(0);
            $table->timestamps();
        });
        (require database_path('migrations/2026_07_10_000001_create_post_letters_table.php'))->up();
        (require database_path('migrations/2026_10_21_000005_add_administration_mail_to_post_letters.php'))->up();
    }

    private function seedPostFixture(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Sender', 'money' => 100, 'is_admin' => true],
            ['id' => 2, 'name' => 'Other', 'money' => 100, 'is_admin' => false],
            ['id' => 3, 'name' => 'Recipient', 'money' => 0, 'is_admin' => false],
        ]);
        DB::table('share_items')->insert(['id' => 1, 'name' => json_encode(['ru' => 'Агат', 'uk' => 'Агат']), 'type' => 'resource', 'is_stackable' => true]);
    }
}
