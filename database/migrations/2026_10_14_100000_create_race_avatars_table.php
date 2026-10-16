<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_avatars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->string('sex', 10);
            $table->string('name', 100);
            $table->string('image_path', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['race_id', 'sex', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_avatars');
    }
};
