<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;

class PrisonStructureSeeder extends Seeder
{
    public function run(): void
    {
        $structure = Structure::query()->where('type', Structure::TYPE_PRISON)->orderBy('id')->first();
        $location = $structure?->location ?? Location::query()->findOrFail(10);

        $structure ??= LocalizedContentSeederQuery::firstOrNewFor(Structure::class, [
            'type' => Structure::TYPE_PRISON,
        ]);

        $structure->forceFill([
            'location_id' => $location->id,
            'name' => [
                'ru' => 'Темница',
                'uk' => 'Темниця',
            ],
            'description' => [
                'ru' => "Вы находитесь в темнице.\n\nКак покинуть темницу:\n• оплатите штраф, если его сумма известна. При необходимости воспользуйтесь банком;\n• если имеется подозрение на взлом или не ясна причина наказания, обратитесь к Администрации;\n• если взломана регистрационная почта, и смена пароля невозможна, обратитесь в техническую поддержку игры;\n• если проклятие имеет срок окончания, можно дождаться его завершения, ничего не предпринимая.",
                'uk' => "Ви перебуваєте в темниці.\n\nЯк покинути темницю:\n• сплатіть штраф, якщо його суму відомо. За потреби скористайтеся банком;\n• якщо є підозра на злам або причина покарання незрозуміла, зверніться до Адміністрації;\n• якщо зламано реєстраційну пошту й змінити пароль неможливо, зверніться до технічної підтримки гри;\n• якщо прокляття має термін завершення, можна дочекатися його завершення, нічого не роблячи.",
            ],
        ])->save();

        $this->command?->info("Темница находится на локации {$structure->location_id} (структура #{$structure->id}).");
    }
}
