<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Reputation\Application\Services\MoveReputationShopToStructure;
use Illuminate\Database\Seeder;

final class MoveTreasureHunterShopToStructureSeeder extends Seeder
{
    public function run(): void
    {
        $shop = app(MoveReputationShopToStructure::class)->execute(
            reputationId: 35,
            nameRu: 'Магазин кладоискателей',
            nameUk: 'Магазин шукачів скарбів',
            descriptionRu: '<p>Карты и припасы для искателей сокровищ. Некоторые товары доступны в ограниченном количестве на игрока.</p>',
            descriptionUk: '<p>Карти та припаси для шукачів скарбів. Деякі товари доступні в обмеженій кількості на гравця.</p>',
        );
        $this->command?->info('Магазин кладоискателей: структура #'.$shop->id.'; товары и персональные лимиты перенесены.');
    }
}
