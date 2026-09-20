<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Services;

use App\Modules\Clan\Domain\Enums\ClanSkillEffectType;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Models\ClanSkillLevel;
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
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Services\News\NewsShortcodeRenderer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class LibraryShortcodeRenderer
{
    private const PREMIUM_SHOP_STRUCTURE_ID = 10;

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

        $html = preg_replace_callback(
            '/\[\[clan_skill_catalog\]\]/i',
            fn (): string => $this->renderClanSkillCatalog(),
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
                    ? route('map.public', [
                        'slug' => $entity->map->slug,
                        'highlight_location' => $entity->id,
                    ])
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

        $premiumArtifactIds = ShopItem::query()
            ->where('structure_id', self::PREMIUM_SHOP_STRUCTURE_ID)
            ->whereIn('share_item_id', $artifacts->modelKeys())
            ->pluck('share_item_id')
            ->flip();

        $gameArtifacts = $artifacts
            ->reject(fn (ShareItem $artifact): bool => $premiumArtifactIds->has($artifact->id));
        $premiumArtifacts = $artifacts
            ->filter(fn (ShareItem $artifact): bool => $premiumArtifactIds->has($artifact->id));

        return '<div class="library-artifact-catalog-groups">'
            .$this->renderArtifactGroup(
                'game',
                'Игровые артефакты',
                'Эти артефакты можно получить игровым способом.',
                $gameArtifacts,
            )
            .$this->renderArtifactGroup(
                'premium',
                'Премиум-артефакты',
                'Эти артефакты приобретаются в премиальном магазине за алмазы.',
                $premiumArtifacts,
            )
            .'</div>';
    }

    private function renderClanSkillCatalog(): string
    {
        $skills = ClanSkillDefinition::query()
            ->with('levels.magicSkill')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($skills->isEmpty()) {
            return '<div class="library-info-block library-info-block--warning">Клановые навыки пока не настроены.</div>';
        }

        $maxLevel = max(1, (int) $skills->max('max_level'));
        $levelHeaders = collect(range(1, $maxLevel))
            ->map(fn (int $level): string => '<th>Ур. '.$level.'</th>')
            ->implode('');
        $rows = $skills
            ->map(fn (ClanSkillDefinition $skill): string => $this->renderClanSkillRow($skill, $maxLevel))
            ->implode('');

        return '<div class="library-clan-skills-wrap">'
            .'<table class="library-clan-skills">'
            .'<thead><tr><th>Навык</th>'.$levelHeaders.'</tr></thead>'
            .'<tbody>'.$rows.'</tbody>'
            .'</table></div>';
    }

    private function renderClanSkillRow(ClanSkillDefinition $skill, int $maxLevel): string
    {
        $levels = $skill->levels->keyBy('level');
        /** @var ClanSkillLevel|null $firstLevel */
        $firstLevel = $levels->get(1);
        $icon = $firstLevel?->magicSkill?->image;
        $iconHtml = $icon
            ? '<img class="library-clan-skills__icon" src="'.e($icon).'" alt="'.e($skill->name).'">'
            : '<span class="library-clan-skills__icon library-clan-skills__icon--empty" aria-hidden="true">✦</span>';
        $cells = '';

        foreach (range(1, $maxLevel) as $levelNumber) {
            /** @var ClanSkillLevel|null $level */
            $level = $levels->get($levelNumber);
            if ($level === null) {
                $cells .= '<td class="library-clan-skills__empty">—</td>';

                continue;
            }

            $effects = collect($level->magicSkill?->effects ?? [])
                ->filter(fn (mixed $effect): bool => is_array($effect))
                ->map(fn (array $effect): string => $this->clanSkillEffectLabel($effect))
                ->filter()
                ->implode('<br>');
            $requirements = 'Клан '.$level->required_clan_level.' ур.';
            if ((int) $level->required_bonus_points > 0) {
                $requirements .= ' · '.format_money((int) $level->required_bonus_points).' очк.';
            }

            $cells .= '<td>'
                .'<strong class="library-clan-skills__bonus">'.($effects !== '' ? $effects : '—').'</strong>'
                .'<small>'.$requirements.'</small>'
                .'</td>';
        }

        return '<tr>'
            .'<th class="library-clan-skills__name"><span class="library-clan-skills__skill">'
            .$iconHtml
            .'<span class="library-clan-skills__data"><strong>'.e($skill->name).'</strong>'
            .'<span>'.e((string) $skill->description).'</span></span>'
            .'</span></th>'
            .$cells
            .'</tr>';
    }

    /** @param array<string, mixed> $effect */
    private function clanSkillEffectLabel(array $effect): string
    {
        $type = ClanSkillEffectType::tryFrom((string) ($effect['type'] ?? ''));
        $value = (float) ($effect['value'] ?? 0);
        $formatted = rtrim(rtrim(number_format(abs($value), 2, '.', ''), '0'), '.');

        return ($value >= 0 ? '+' : '−')
            .e($formatted)
            .(! empty($effect['is_percent']) ? '%' : '')
            .' '.e($type?->label() ?? (string) ($effect['type'] ?? 'Бонус'));
    }

    /** @param Collection<int, ShareItem> $artifacts */
    private function renderArtifactGroup(string $type, string $title, string $description, Collection $artifacts): string
    {
        $cards = $artifacts
            ->map(fn (ShareItem $artifact): string => $this->renderArtifact($artifact))
            ->implode('');

        $content = $cards !== ''
            ? '<div class="library-artifact-catalog">'.$cards.'</div>'
            : '<div class="library-artifact-catalog-group__empty">Артефакты этой группы пока не добавлены.</div>';

        return '<section class="library-artifact-catalog-group library-artifact-catalog-group--'.e($type).'">'
            .'<header class="library-artifact-catalog-group__header">'
            .'<strong>'.e($title).'</strong>'
            .'<span>'.e($description).'</span>'
            .'</header>'
            .$content
            .'</section>';
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
