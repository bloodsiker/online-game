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
        Schema::create('magic_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название скилла
            $table->string('slug')->unique();          // Уникальный код (fireball, heal и т.д.)
            $table->text('description')->nullable();
            $table->integer('level')->default(1);
            $table->enum('type', ['attack', 'defense', 'buff', 'debuff', 'heal', 'utility']);
            $table->integer('mana_cost')->default(0);              // Стоимость маны
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->integer('base_healing')->default(0);
            $table->integer('cooldown');               // Кулдаун в секундах
            $table->enum('target_type', ['self', 'all', 'enemy']); // Тип цели
            $table->boolean('is_passive')->default(false); // Пассивный или активный
            $table->json('effects')->nullable();       // JSON с эффектами (damage, heal, buff_ids и т.д.)
            $table->timestamps();
        });

        Schema::create('effects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название эффекта
            $table->string('slug')->unique();          // Уникальный код (regen, poison, stun)
            $table->enum('type', ['buff', 'debuff', 'neutral']);
            $table->text('description')->nullable();
            $table->integer('chance')->default(0);

            // Параметры эффекта
            $table->integer('duration');               // Длительность в секундах (0 = бесконечно до снятия)
            $table->boolean('is_stackable')->default(false); // Можно ли стакать
            $table->integer('max_stacks')->default(1);

            // Что делает эффект за тик (если дот/хот)
            $table->integer('tick_interval')->default(1); // Каждые N секунд
            $table->integer('value_per_tick')->nullable(); // Урон/лечение за тик

            // Модификаторы статов
            $table->json('stat_modifiers')->nullable(); // Пример: {"strength": 10, "speed": -20}

            $table->boolean('is_dispellable')->default(true); // Можно ли снять
            $table->timestamps();
        });

        Schema::create('magic_skill_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magic_skill_id')->constrained()->onDelete('cascade');
            $table->foreignId('effect_id')->constrained()->onDelete('cascade');
            $table->integer('chance')->default(100);
            $table->timestamps();
        });

        Schema::create('player_magic_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('magic_skill_id')->constrained('magic_skills')->onDelete('cascade');
            $table->timestamp('cooldown_end_at')->nullable(); // Когда закончится кулдаун
            $table->boolean('is_equipped')->default(false);

            $table->unique(['player_id', 'magic_skill_id']);
        });

        Schema::create('player_active_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('effect_id')->constrained('effects')->onDelete('cascade');
            $table->foreignId('source_player_id')->nullable()->constrained('players')->onDelete('set null'); // Кто наложил
            $table->foreignId('source_magic_skill_id')->nullable()->constrained('magic_skills')->onDelete('set null'); // Каким скиллом

            $table->timestamp('applied_at');           // Когда наложен
            $table->timestamp('expires_at')->nullable(); // Когда истекает (null = пока не снимут)
            $table->integer('stacks')->default(1);     // Текущее количество стаков
            $table->integer('current_value')->nullable(); // Остаток значения (для DoT/HoT)

            $table->timestamps();

            // Один и тот же эффект от одного источника не должен дублироваться
            $table->unique(
                ['player_id', 'effect_id', 'source_player_id', 'source_magic_skill_id'],
                'uniq_player_effect_source'
            );
        });

        Schema::create('monster_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->onDelete('cascade');
            $table->foreignId('effect_id')->constrained('effects')->onDelete('cascade');
            $table->integer('chance')->default(0);

            $table->timestamps();
        });

        Schema::create('monster_active_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->onDelete('cascade');
            $table->foreignId('effect_id')->constrained('effects')->onDelete('cascade');

            $table->timestamp('applied_at');           // Когда наложен
            $table->timestamp('expires_at')->nullable(); // Когда истекает (null = пока не снимут)
            $table->integer('stacks')->default(1);     // Текущее количество стаков
            $table->integer('current_value')->nullable(); // Остаток значения (для DoT/HoT)

            $table->timestamps();

            // Один и тот же эффект от одного источника не должен дублироваться
            $table->unique(['monster_id', 'effect_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_active_effects');
        Schema::dropIfExists('monster_effects');
        Schema::dropIfExists('player_active_effects');
        Schema::dropIfExists('player_magic_skills');
        Schema::dropIfExists('magic_skill_effects');
        Schema::dropIfExists('effects');
        Schema::dropIfExists('magic_skills');
    }
};
