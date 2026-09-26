<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magic_skill_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedBigInteger('magic_skill_id')->unique();
            $table->timestamps();

            $table->foreign('share_item_id')->references('id')->on('share_items')->onDelete('cascade');
            $table->foreign('magic_skill_id')->references('id')->on('magic_skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_skill_books');
    }
};
