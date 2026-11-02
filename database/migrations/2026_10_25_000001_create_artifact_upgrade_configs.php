<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_upgrade_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->unique()->constrained('share_items')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('gold_cost')->default(0);
            $table->timestamps();
        });
        Schema::create('artifact_upgrade_materials', function (Blueprint $table): void {
            $table->foreignId('config_id')->constrained('artifact_upgrade_configs')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->restrictOnDelete();
            $table->unsignedInteger('count');
            $table->primary(['config_id', 'share_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_upgrade_materials');
        Schema::dropIfExists('artifact_upgrade_configs');
    }
};
