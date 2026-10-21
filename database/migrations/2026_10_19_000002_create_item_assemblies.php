<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_assemblies', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type', 32)->index();
            $table->string('result_mode', 16)->default('random');
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            // Audit snapshot permits an exact rollback without changing item IDs.
            $table->unsignedBigInteger('legacy_recipe_id')->nullable()->unique();
            $table->json('legacy_recipe_snapshot')->nullable();
            $table->timestamps();
        });
        Schema::create('item_assembly_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_assembly_id')->constrained('item_assemblies')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->restrictOnDelete();
            $table->unsignedInteger('count')->default(1);
            $table->unique(['item_assembly_id', 'share_item_id']);
        });
        Schema::create('item_assembly_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_assembly_id')->constrained('item_assemblies')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->restrictOnDelete();
            $table->unsignedInteger('count')->default(1);
            $table->unsignedInteger('weight')->default(1);
            $table->unique(['item_assembly_id', 'share_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_assembly_rewards');
        Schema::dropIfExists('item_assembly_components');
        Schema::dropIfExists('item_assemblies');
    }
};
