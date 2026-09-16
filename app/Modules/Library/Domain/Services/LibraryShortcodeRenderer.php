<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Services\News\NewsShortcodeRenderer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class LibraryShortcodeRenderer
{
    /** @var array<string, array<int, Model>> */
    private array $entities = [];

    public function __construct(private readonly NewsShortcodeRenderer $itemRenderer) {}

    public function render(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $html = preg_replace_callback(
            '/\[\[injury_catalog\]\]/i',
            fn (): string => $this->renderInjuryCatalog(),
            $html,
        ) ?? $html;

        $html = preg_replace_callback(
            '/\[\[artifact_catalog\]\]/i',
            fn (): string => $this->renderArtifactCatalog(),
            $html,
        ) ?? $html;

        // Каталоги могут сами добавлять шорткоды предметов, поэтому предметы
        // рендерятся после динамических библиотечных блоков одним общим проходом.
        $html = $this->itemRenderer->render($html);

        $matches = [];
        preg_match_all($this->pattern(), $html, $matches, PREG_SET_ORDER);

        if ($matches === []) {
            return $html;
        }

        $this->loadEntities($matches);

        return preg_replace_callback($this->pattern(), function (array $match): string {
            $type = strtolower($match[1]);
            $entity = $this->entities[$type][(int) $match[2]] ?? null;
            $display = strtolower($match[3] ?? 'block');

            return $entity ? $this->renderEntity($type, $entity, $display) : $match[0];
        }, $html) ?? $html;
    }

    public function tooltipScript(): string
    {
        return $this->itemRenderer->tooltipScript();
    }

    private function pattern(): string
    {
        return '/\[\[(monster|npc|reputation|map|location):(\d+)(?:\s*;\s*display\s*:\s*(block|inline))?\s*\]\]/i';
    }

    /** @param array<int, array<int, string>> $matches */
    private function loadEntities(array $matches): void
    {
        $idsByType = [];

        foreach ($matches as $match) {
            $idsByType[strtolower($match[1])][] = (int) $match[2];
        }

        foreach ($idsByType as $type => $ids) {
            $ids = array_values(array_unique($ids));
            $query = match ($type) {
                'monster' => Monster::query(),
                'npc' => Npc::query()->with('location'),
                'reputation' => Reputation::query(),
                'map' => Map::query(),
                'location' => Location::query()->with('map'),
            };

            $this->entities[$type] = $query->whereKey($ids)->get()->keyBy('id')->all();
        }
    }

    private function renderEntity(string $type, Model $entity, string $display): string
    {
        [$label, $title, $image, $details, $url] = match ($type) {
            'monster' => [
                'Монстр',
                $entity->name,
                $entity->image,
                sprintf('%d ур. · HP %s · Урон %s–%s', $entity->lvl, format_money($entity->hp), format_money($entity->min_dmg), format_money($entity->max_dmg)),
                route('info.monster.catalog', ['id' => $entity->id]),
            ],
            'npc' => [
                'НПС',
                $entity->name,
                $entity->image,
                $entity->location?->name ? 'Локация: '.$entity->location->name : Str::limit(strip_tags((string) $entity->description), 90),
                route('info.npc', ['uuid' => $entity->uuid]),
            ],
            'reputation' => [
                'Репутация',
                $entity->name,
                resolve_storage_image_url($entity->icon),
                Str::limit(strip_tags((string) $entity->description), 90),
                route('reputation.index', ['id' => $entity->id]),
            ],
            'map' => [
                'Карта',
                $entity->name,
                null,
                null,
                $entity->slug ? route('map.public', ['slug' => $entity->slug]) : route('map'),
            ],
            'location' => [
                'Локация',
                $entity->name,
                $entity->image,
                $entity->map?->name ? 'Карта: '.$entity->map->name : Str::limit(strip_tags((string) $entity->description), 90),
                $entity->map?->slug
                    ? route('map.public', ['slug' => $entity->map->slug]).'#'.$entity->id
                    : route('map'),
            ],
        };

        $imageHtml = $image
            ? '<img class="library-entity-card__image" src="'.e(asset($image)).'" alt="'.e((string) $title).'">'
            : '<span class="library-entity-card__image library-entity-card__image--empty">?</span>';
        $popupAttribute = in_array($type, ['monster', 'npc'], true) ? ' data-library-info-popup' : '';

        if ($display === 'inline') {
            $inlineImage = $image
                ? '<img class="library-entity-line__image" src="'.e(asset($image)).'" alt="">'
                : '';

            return '<a class="library-entity-line library-entity-line--'.e($type).'" href="'.e($url).'"'.$popupAttribute.'>'
                .$inlineImage
                .'<span class="library-entity-line__type">'.e($label).':</span> '
                .'<strong class="library-entity-line__title">'.e((string) $title).'</strong>'
                .($details ? '<span class="library-entity-line__details"> — '.e($details).'</span>' : '')
                .'</a>';
        }

        return '<a class="library-entity-card library-entity-card--'.e($type).'" href="'.e($url).'"'.$popupAttribute.'>'
            .$imageHtml
            .'<span class="library-entity-card__body">'
            .'<span class="library-entity-card__type">'.e($label).'</span>'
            .'<strong class="library-entity-card__title">'.e((string) $title).'</strong>'
            .'<span class="library-entity-card__details">'.e($details ?: 'Подробнее').'</span>'
            .'</span></a>';
    }

    private function renderInjuryCatalog(): string
    {
        $injuryTypes = InjuryType::query()
            ->where('is_active', true)
            ->where('drop_weight', '>', 0)
            ->orderBy('severity')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (InjuryType $injuryType): string => $injuryType->body_part->value)
            ->map(fn ($types) => $types->groupBy(fn (InjuryType $injuryType): int => $injuryType->severity->value));

        if ($injuryTypes->isEmpty()) {
            return '<div class="library-info-block library-info-block--warning">Активные травмы пока не настроены.</div>';
        }

        $headers = collect(InjurySeverity::cases())
            ->map(fn (InjurySeverity $severity): string => '<th>'.e($severity->label()).'</th>')
            ->implode('');
        $rows = '';

        foreach (InjuryBodyPart::cases() as $bodyPart) {
            $bySeverity = $injuryTypes->get($bodyPart->value);
            if ($bySeverity === null) {
                continue;
            }

            $cells = '';
            foreach (InjurySeverity::cases() as $severity) {
                $cards = collect($bySeverity->get($severity->value, collect()))
                    ->map(fn (InjuryType $injuryType): string => $this->renderInjuryType($injuryType))
                    ->implode('');

                $cells .= '<td>'.($cards !== '' ? $cards : '—').'</td>';
            }

            $rows .= '<tr><th class="library-injury-catalog__part">'.e($bodyPart->label()).'</th>'.$cells.'</tr>';
        }

        return '<div class="library-injury-catalog-wrap"><table class="library-injury-catalog">'
            .'<thead><tr><th>Часть тела</th>'.$headers.'</tr></thead>'
            .'<tbody>'.$rows.'</tbody></table></div>';
    }

    private function renderInjuryType(InjuryType $injuryType): string
    {
        $image = $injuryType->image
            ? '<img class="library-injury-catalog__icon" src="'.e($injuryType->image).'" alt="'.e($injuryType->name).'">'
            : '<span class="library-injury-catalog__icon library-injury-catalog__icon--fallback" aria-hidden="true">+</span>';

        return '<div class="library-injury-catalog__entry library-injury-catalog__entry--'.$injuryType->severity->value.'">'
            .$image
            .'<span class="library-injury-catalog__data">'
            .'<strong>'.e($injuryType->name).'</strong>'
            .'<span>'.e($injuryType->durationMinutes().' мин.').'</span>'
            .'<span>'.e($this->injuryModifiersLabel($injuryType)).'</span>'
            .'</span></div>';
    }

    private function injuryModifiersLabel(InjuryType $injuryType): string
    {
        $labels = collect($injuryType->stat_modifiers ?? [])
            ->filter(fn (mixed $modifier): bool => is_array($modifier) && isset($modifier['stat'], $modifier['value']))
            ->map(function (array $modifier): string {
                $stat = PlayerStatKey::tryFrom((string) $modifier['stat'])?->label() ?? (string) $modifier['stat'];
                $value = (float) $modifier['value'];
                $formatted = rtrim(rtrim(number_format(abs($value), 2, '.', ''), '0'), '.');
                $unit = ! empty($modifier['is_percent']) ? '%' : '';

                return $stat.' '.($value >= 0 ? '+' : '−').$formatted.$unit;
            })
            ->implode(', ');

        return $labels !== '' ? $labels : 'Без штрафа';
    }

    private function renderArtifactCatalog(): string
    {
        $artifacts = ShareItem::query()
            ->with(['stats', 'requirements.skill'])
            ->where('type', ShareItemType::ARTIFACT->value)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($artifacts->isEmpty()) {
            return '<div class="library-info-block library-info-block--warning">Активные артефакты пока не настроены.</div>';
        }

        $cards = $artifacts
            ->map(fn (ShareItem $artifact): string => $this->renderArtifact($artifact))
            ->implode('');

        return '<div class="library-artifact-catalog">'.$cards.'</div>';
    }

    private function renderArtifact(ShareItem $artifact): string
    {
        $rarity = $artifact->rarity;
        $stats = $artifact->stats
            ->map(fn (ShareItemStat $stat): string => $this->artifactStatLabel($stat))
            ->implode('');
        $requirements = $artifact->requirements
            ->map(fn ($requirement): string => e($requirement->label()).' '.e((string) $requirement->min_value))
            ->implode(', ');
        $kind = $artifact->count_use > 0
            ? 'Расходуемый · '.e((string) $artifact->count_use).' исп.'
            : 'Постоянный талисман';
        $description = Str::limit(trim(strip_tags((string) $artifact->description)), 150);

        return '<article class="library-artifact-catalog__entry">'
            .'<div class="library-artifact-catalog__icon">[[item:'.$artifact->id.']]</div>'
            .'<div class="library-artifact-catalog__data">'
            .'<strong class="library-artifact-catalog__name" style="color:'.e($rarity->color()).'">'.e($artifact->name).'</strong>'
            .'<span class="library-artifact-catalog__meta">'.e($rarity->label()).' · '.$kind.'</span>'
            .($stats !== '' ? '<span class="library-artifact-catalog__stats">'.$stats.'</span>' : '')
            .($requirements !== '' ? '<span class="library-artifact-catalog__requirements"><b>Требования:</b> '.$requirements.'</span>' : '')
            .($description !== '' ? '<span class="library-artifact-catalog__description">'.e($description).'</span>' : '')
            .'</div></article>';
    }

    private function artifactStatLabel(ShareItemStat $stat): string
    {
        $value = (float) $stat->value;
        $formatted = rtrim(rtrim(number_format(abs($value), 2, '.', ''), '0'), '.');

        return '<span><b>'.e($stat->stat_type->label()).':</b> '
            .($value >= 0 ? '+' : '−')
            .e($formatted)
            .($stat->isPercent() ? '%' : '')
            .'</span>';
    }
}
