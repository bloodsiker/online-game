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
            $table->text('description')->nullable();
            $table->integer('lvl')->default(1);
            $table->string('icon')->nullable();
//            $table->unsignedBigInteger('owner_id');
            $table->foreignId('owner_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('clan_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'Лидер', 'Офицер', 'Член'
            $table->json('permissions'); // ['invite', 'kick', 'withdraw', ...]
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clan_logs');
        Schema::dropIfExists('clan_join_requests');
        Schema::dropIfExists('clan_members');
        Schema::dropIfExists('clan_roles');
        Schema::dropIfExists('clans');
    }
};
