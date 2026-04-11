<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leader_user_id');
            $table->string('invite_code', 16)->unique();
            $table->unsignedTinyInteger('max_players')->default(4);
            $table->enum('status', ['open', 'in_dungeon', 'disbanded'])->default('open');
            $table->timestamps();

            $table->foreign('leader_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('leader_user_id');
        });

        Schema::create('party_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_ready')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['party_id', 'user_id']);
            $table->unique('user_id'); // игрок только в одной пати

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_members');
        Schema::dropIfExists('parties');
    }
};