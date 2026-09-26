<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_item_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->enum('type', ['level', 'stat', 'skill']);
            $table->string('stat_key', 20)->nullable();   // lvl, str, agil, int, mud, intel
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedSmallInteger('min_value');
            $table->timestamps();

            $table->foreign('share_item_id')->references('id')->on('share_items')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_item_requirements');
    }
};