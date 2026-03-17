<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('news_1')->nullable();
            $table->text('news_2')->nullable();
            $table->text('news_3')->nullable();
            $table->text('description')->nullable();
            $table->integer('lvl')->default(1);
            $table->unsignedInteger('warehouse_capacity')->default(50);
            $table->string('icon')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('clan_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans');
            $table->string('name'); // 'Лидер', 'Офицер', 'Член'
//            $table->json('permissions'); // ['invite', 'kick', 'withdraw', ...]
            $table->unsignedBigInteger('permissions')->default(0);
            $table->boolean('is_leader')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('clan_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('role_id')->constrained('clan_roles');
            $table->timestamps();

            $table->unique(['clan_id', 'user_id']);
        });

        Schema::create('clan_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status', 20);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['clan_id', 'user_id']);
        });

        Schema::create('clan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action'); // 'joined', 'left', 'promoted', etc.
            $table->text('details')->nullable();
            $table->timestamps();
        });

        Schema::create('clan_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures');
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('depositor_user_id')->constrained('users');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();
        });

        Schema::create('clan_warehouse_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('action', array_column(\App\Enums\ClanWarehouseAction::cases(), 'value'));
            $table->unsignedInteger('count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clan_warehouse_logs');
        Schema::dropIfExists('clan_warehouses');
        Schema::dropIfExists('clan_logs');
        Schema::dropIfExists('clan_join_requests');
        Schema::dropIfExists('clan_members');
        Schema::dropIfExists('clan_roles');
        Schema::dropIfExists('clans');
    }
};
