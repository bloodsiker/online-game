<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_dialogues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->text('description');
            $table->string('reply_text')->default('Далее');
            $table->timestamps();

            $table->index(['quest_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_dialogues');
    }
};
