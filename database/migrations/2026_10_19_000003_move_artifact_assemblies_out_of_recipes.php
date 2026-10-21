<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $recipes = DB::table('share_recipes')->where('is_random_result', true)->where('unlock_type', 'single_use')->orderBy('id')->lockForUpdate()->get();
            foreach ($recipes as $recipe) {
                $anchor = DB::table('share_items')->where('id', $recipe->share_item_id)->first();
                $rewards = DB::table('share_recipe_results')->where('share_recipe_id', $recipe->id)->orderBy('id')->get();
                // Real recipe books keep their existing semantics. Only part-based artifact sets move.
                $anchorName = $anchor === null ? '' : (json_decode($anchor->name, true)['ru'] ?? $anchor->name);
                $recipeShard = $anchor !== null && $anchor->type === 'recipe' && str_starts_with($anchorName, 'Осколок ');
                if ($anchor === null || (! in_array($anchor->type, ['resource', 'misc'], true) && ! $recipeShard) || $rewards->isEmpty() || (int) $recipe->percent !== 100) {
                    continue;
                }
                $artifactCount = DB::table('share_items')->whereIn('id', $rewards->pluck('share_item_id'))->where('type', 'artifact')->count();
                if ($artifactCount !== $rewards->count()) {
                    continue;
                }
                $components = DB::table('share_recipe_has_items')->where('share_recipe_id', $recipe->id)->orderBy('id')->get();
                if ($components->isEmpty() || $components->contains(fn ($row) => $row->count < 1) || $rewards->contains(fn ($row) => $row->chance < 1)) {
                    throw new RuntimeException('Invalid legacy artifact assembly '.$recipe->id);
                }
                if (Schema::hasTable('player_recipes') && DB::table('player_recipes')->where('share_recipe_id', $recipe->id)->exists()) {
                    throw new RuntimeException('Cannot move a learned recipe '.$recipe->id);
                }
                $transferComponents = clone $components;
                if ($recipeShard && ! $components->contains('share_item_id', $anchor->id)) {
                    $transferComponents->push((object) ['share_item_id' => $anchor->id, 'count' => 1]);
                }
                $id = DB::table('item_assemblies')->insertGetId([
                    'name' => json_encode(['ru' => 'Сборка артефакта', 'uk' => 'Збірка артефакту'], JSON_UNESCAPED_UNICODE),
                    'type' => 'artifact', 'result_mode' => 'random', 'is_active' => true, 'sort_order' => 0,
                    'legacy_recipe_id' => $recipe->id,
                    'legacy_recipe_snapshot' => json_encode(['recipe' => $recipe, 'components' => $components, 'rewards' => $rewards, 'anchor_type' => $anchor->type], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($transferComponents as $component) {
                    DB::table('item_assembly_components')->insert(['item_assembly_id' => $id, 'share_item_id' => $component->share_item_id, 'count' => $component->count]);
                }
                foreach ($rewards as $reward) {
                    DB::table('item_assembly_rewards')->insert(['item_assembly_id' => $id, 'share_item_id' => $reward->share_item_id, 'count' => 1, 'weight' => $reward->chance]);
                }
                if (DB::table('item_assembly_components')->where('item_assembly_id', $id)->count() !== $transferComponents->count()
                    || DB::table('item_assembly_rewards')->where('item_assembly_id', $id)->count() !== $rewards->count()) {
                    throw new RuntimeException('Assembly transfer verification failed.');
                }
                if ($recipeShard) {
                    DB::table('share_items')->where('id', $anchor->id)->update(['type' => 'resource']);
                }
                DB::table('share_recipe_results')->where('share_recipe_id', $recipe->id)->delete();
                DB::table('share_recipe_has_items')->where('share_recipe_id', $recipe->id)->delete();
                DB::table('share_recipes')->where('id', $recipe->id)->delete();
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('item_assemblies')->whereNotNull('legacy_recipe_snapshot')->get() as $assembly) {
                $snapshot = json_decode($assembly->legacy_recipe_snapshot, true, 512, JSON_THROW_ON_ERROR);
                if (DB::table('share_recipes')->where('id', $snapshot['recipe']['id'])->exists()) {
                    throw new RuntimeException('Legacy recipe ID is already in use; rollback stopped.');
                }
                DB::table('share_recipes')->insert($snapshot['recipe']);
                DB::table('share_items')->where('id', $snapshot['recipe']['share_item_id'])->update(['type' => $snapshot['anchor_type']]);
                foreach (['components' => 'share_recipe_has_items', 'rewards' => 'share_recipe_results'] as $key => $table) {
                    if ($snapshot[$key] !== []) {
                        DB::table($table)->insert($snapshot[$key]);
                    }
                }
                DB::table('item_assembly_components')->where('item_assembly_id', $assembly->id)->delete();
                DB::table('item_assembly_rewards')->where('item_assembly_id', $assembly->id)->delete();
                DB::table('item_assemblies')->where('id', $assembly->id)->delete();
            }
        });
    }
};
