<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->boolean('is_random_result')->default(false)->after('kraft_item_id');
        });

        // kraft_item_id остаётся обязательным для обычных (детерминированных)
        // рецептов, но у рецептов с is_random_result=true результат берётся
        // из share_recipe_results, а не из этой колонки - делаем её nullable.
        DB::statement('ALTER TABLE share_recipes MODIFY kraft_item_id BIGINT UNSIGNED NULL');

        Schema::create('share_recipe_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_recipe_id')->constrained('share_recipes')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('chance')->default(1)->comment('Вес в пуле: шанс предмета = chance / сумма chance всех записей пула');
            $table->timestamps();

            $table->unique(['share_recipe_id', 'share_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_recipe_results');

        // kraft_item_id осознанно оставляем nullable при откате: рецепты с
        // is_random_result=true (если успели создать) имеют NULL здесь, и
        // принудительный NOT NULL на живых данных упал бы на FK/constraint.
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->dropColumn('is_random_result');
        });
    }
};
