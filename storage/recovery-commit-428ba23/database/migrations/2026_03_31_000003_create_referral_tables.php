<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кто кого пригласил
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id');
            $table->unsignedBigInteger('referred_user_id')->unique();
            $table->timestamps();

            $table->foreign('referrer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Этапы наград (настраиваются в админке)
        Schema::create('referral_reward_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('level_threshold');   // при достижении этого уровня
            $table->string('reward_type', 20);                 // gold | diamond | item
            $table->unsignedBigInteger('reward_item_id')->nullable(); // share_item_id если type=item
            $table->unsignedInteger('reward_value');           // кол-во монет/алмазов или кол-во предметов
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('reward_item_id')->references('id')->on('share_items')->onDelete('set null');
        });

        // Какие награды уже выданы по каждому реферралу
        Schema::create('referral_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_id');
            $table->unsignedBigInteger('stage_id');
            $table->timestamp('claimed_at')->useCurrent();

            $table->unique(['referral_id', 'stage_id']);
            $table->foreign('referral_id')->references('id')->on('referrals')->onDelete('cascade');
            $table->foreign('stage_id')->references('id')->on('referral_reward_stages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_reward_claims');
        Schema::dropIfExists('referral_reward_stages');
        Schema::dropIfExists('referrals');
    }
};