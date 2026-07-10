<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_letters', function (Blueprint $table) {
            $table->id();
            // null = системное письмо
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('subject', 64);
            $table->text('text');

            // Приложенные деньги: списываются при отправке, зачисляются кнопкой «Забрать»
            $table->unsignedInteger('money')->default(0);
            $table->timestamp('money_claimed_at')->nullable();

            // Вложенный предмет (может быть несколько штук), выдаётся той же кнопкой «Забрать»
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->unsignedInteger('item_amount')->default(1);
            $table->timestamp('item_claimed_at')->nullable();

            $table->timestamp('read_at')->nullable();

            // Удаление у каждой стороны своё
            $table->timestamp('sender_deleted_at')->nullable();
            $table->timestamp('recipient_deleted_at')->nullable();

            $table->timestamps();

            $table->index(['recipient_user_id', 'recipient_deleted_at']);
            $table->index(['sender_user_id', 'sender_deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_letters');
    }
};
