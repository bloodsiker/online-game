<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_gates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->unsignedBigInteger('share_item_id');
            $table->enum('mode', ['teleport_use', 'presence_pass'])->default('presence_pass');
            $table->boolean('consume_item')->default(false);
            $table->string('button_label')->nullable();
            $table->timestamps();

            $table->foreign('from_location_id')->references('id')->on('locations')->cascadeOnDelete();
            $table->foreign('to_location_id')->references('id')->on('locations')->cascadeOnDelete();
            $table->foreign('share_item_id')->references('id')->on('share_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_gates');
    }
};
