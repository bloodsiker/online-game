<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class TreasureHunterNpcSeeder extends Seeder
{
    private const NPC_UUID = '73400611-2b3f-4ee4-9004-202611010004';

    public function run(): void
    {
        DB::transaction(function (): void {
            Location::findOrFail(4);
            $reputationId = TreasureMapConfig::orderBy('id')->value('reputation_id');
            $reputation = Reputation::whereKey($reputationId)->lockForUpdate()->firstOrFail();
            $npc = $reputation->npc ?? Npc::where('uuid', self::NPC_UUID)->first();
            if ($npc === null) {
                $npc = new Npc([
                    'name' => ['ru' => 'Кладоискатель Ренвар', 'uk' => 'Шукач скарбів Ренвар'],
                    'description' => [
                        'ru' => '<p>Ренвар встречает посетителей за столом, заваленным потёртыми картами, латунными компасами и обломками древних печатей. На полях его записей тесно от пометок, но мест, где ещё не ступала нога кладоискателя, по-прежнему немало.</p><p>Он представляет братство кладоискателей и присматривает за его лавкой. Здесь ценят не громкие обещания, а найденные тайники и заслуженную репутацию.</p>',
                        'uk' => '<p>Ренвар зустрічає відвідувачів за столом, заваленим потертими картами, латунними компасами та уламками давніх печаток. На полях його записів тісно від позначок, але місць, де ще не ступала нога шукача скарбів, досі чимало.</p><p>Він представляє братство шукачів скарбів і наглядає за його крамницею. Тут цінують не гучні обіцянки, а знайдені схованки та заслужену репутацію.</p>',
                    ],
                    'location_id' => 4, 'hide_location' => false, 'is_active' => true,
                    'image' => '/img/npc/npc_gnom.jpg',
                ]);
                $npc->uuid = self::NPC_UUID;
                $this->saveLocalized($npc, ['name', 'description']);
            }
            if ((int) $reputation->npc_id !== (int) $npc->id) {
                $reputation->npc_id = $npc->id;
                $reputation->save();
            }
            $category = $reputation->categories()->first();
            if ($category === null) {
                $category = ShareStructureCategory::where('name->ru', 'Товары кладоискателя')->first();
                if ($category === null) {
                    $category = new ShareStructureCategory([
                        'name' => ['ru' => 'Товары кладоискателя', 'uk' => 'Товари шукача скарбів'], 'is_active' => true,
                    ]);
                    $this->saveLocalized($category, ['name']);
                }
                $reputation->categories()->syncWithoutDetaching([$category->id]);
            }
            $this->command?->info('NPC #'.$npc->id.' — '.$npc->name.'; локация #'.$npc->location_id.'; магазин репутации #'.$reputation->id.'.');
        });
    }

    private function saveLocalized(Model $model, array $fields): void
    {
        foreach ($fields as $field) {
            if (Schema::hasColumn($model->getTable(), $field.'_legacy')) {
                $model->setAttribute($field.'_legacy', $model->getTranslation($field, 'ru'));
            }
        }
        $model->save();
    }
}
