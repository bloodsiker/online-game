<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillRequirement;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\PassiveBonusStatType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Страница «Наставника магии» — гримуары из рюкзака с заклинанием, которое они дают, см. LearnMagicSkillFromBook. */
class GetMageTrainerPage
{
    public function __construct(private readonly ItemTooltipCollector $collector) {}

    public function execute(User $user, int $structureId): array
    {
        $structure = Structure::query()->findOrFail($structureId);
        abort_unless($structure->isMageTrainer(), 404);

        $player = $user->player;
        $learnedSkillIds = DB::table('player_magic_skills')->where('player_id', $player->id)->pluck('magic_skill_id')->all();
        $skillLevels = DB::table('player_skills')->where('player_id', $player->id)->pluck('lvl', 'skill_id');

        $booksByShareItem = MagicSkillBook::query()
            ->with(['magicSkill.requirements.skill', 'magicSkill.skillEffects'])
            ->get()
            ->keyBy('share_item_id');

        $slots = Backpack::query()
            ->with(['item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('items.share_item_id', $booksByShareItem->keys())
            ->select('backpacks.*')
            ->get();

        $books = $slots->map(function (Backpack $slot) use ($booksByShareItem, $learnedSkillIds, $player, $skillLevels): ?array {
            $shareItem = $slot->item->itemInfo;
            $book = $booksByShareItem->get($shareItem->id);
            $skill = $book?->magicSkill;

            if ($skill === null) {
                return null;
            }

            $requirements = $skill->requirements
                ->map(fn (MagicSkillRequirement $req): array => $this->mapRequirement($req, $player, $skillLevels))
                ->sortBy('label')
                ->values();

            $alreadyLearned = in_array($skill->id, $learnedSkillIds, true);

            return [
                'shareItem' => $shareItem,
                'count' => (int) $slot->count,
                'skill' => $skill,
                'requirements' => $requirements,
                'alreadyLearned' => $alreadyLearned,
                'canLearn' => ! $alreadyLearned && $requirements->every(fn (array $req): bool => $req['met']),
            ];
        })->filter()->sortBy(fn (array $book): int => $book['skill']->level)->values();

        $this->collector->collectFrom(new ShareItemTooltipStrategy($books->pluck('shareItem')));
        foreach ($books as $book) {
            $this->collector->add($this->buildSkillTooltip($book['skill'], $book['shareItem']->rarity->color()));
        }

        return [
            'structure' => $structure,
            'user' => $user,
            'books' => $books,
            'itemTooltipScript' => $this->collector->renderScript(),
        ];
    }

    private function buildSkillTooltip(MagicSkill $skill, string $color): ItemTooltipDto
    {
        $typeLabel = match (true) {
            (bool) $skill->is_passive => __('Пассивка'),
            $skill->type === 'attack' => __('Атакующее заклинание'),
            $skill->type === 'debuff' => __('Дебафф'),
            $skill->type === 'heal' => __('Лечение'),
            default => __('Бафф'),
        };

        $stats = [];
        if ($skill->mana_cost > 0) {
            $stats[] = ['title' => __('Мана'), 'value' => (string) $skill->mana_cost];
        }
        if ($skill->max_damage > 0) {
            $stats[] = ['title' => __('Урон'), 'value' => $skill->min_damage.' – '.$skill->max_damage];
        }
        if ($skill->base_healing > 0) {
            $stats[] = ['title' => __('Лечение'), 'value' => (string) $skill->base_healing];
        }
        if ($skill->cooldown > 0) {
            $stats[] = ['title' => __('Перезарядка'), 'value' => $skill->cooldown.' '.__('сек.')];
        }
        foreach ($skill->skillEffects as $effect) {
            $stats[] = [
                'title' => __('Эффект'),
                'value' => $effect->name.' ('.$effect->pivot->chance.'%, '.$effect->pivot->duration_seconds.' '.__('сек.').')',
            ];
        }
        // Пассивные бонусы — прямой JSON на самом скилле (magic_skills.effects,
        // админка «Пассивные бонусы»), отдельный от skillEffects источник,
        // см. PlayerStatService::fromPassiveSkills() Source 1.
        foreach ($skill->effects ?? [] as $entry) {
            if (! is_array($entry) || empty($entry['type'])) {
                continue;
            }
            $statLabel = PassiveBonusStatType::tryFrom($entry['type'])?->label() ?? $entry['type'];
            $sign = ((float) ($entry['value'] ?? 0)) >= 0 ? '+' : '';
            $stats[] = [
                'title' => $statLabel,
                'value' => $sign.($entry['value'] ?? 0).(! empty($entry['is_percent']) ? '%' : ''),
            ];
        }

        return new ItemTooltipDto(
            id: 'magic_skill_'.$skill->id,
            title: $skill->name,
            color: $color,
            image: (string) ($skill->image ?? ''),
            kind: $typeLabel,
            price: '',
            diamond: '',
            lev: [],
            skills: [],
            desc: (string) ($skill->description ?? ''),
            store: false,
            nogive: false,
            noweight: false,
            nosell: false,
            stats: $stats,
            // Тот же формат, что ItemTooltipStatsBuilder::buildRequirements() —
            // item_tooltip.js сам считает met/unmet через itemTooltipCheckRequirement()
            // по window.playerStats by type/stat_key/skill_id/min_value, а не по готовой строке.
            requirements: $skill->requirements->map(fn (MagicSkillRequirement $req): array => [
                'title' => $req->label(),
                'type' => $req->type->value,
                'stat_key' => $req->stat_key,
                'skill_id' => $req->skill_id,
                'min_value' => $req->min_value,
            ])->all(),
        );
    }

    /** @param  Collection<int, int>  $skillLevels */
    private function mapRequirement(MagicSkillRequirement $req, Player $player, $skillLevels): array
    {
        $current = match ($req->type) {
            MagicSkillRequirementType::SKILL => (int) ($skillLevels[$req->skill_id] ?? 0),
            MagicSkillRequirementType::STAT => $this->statValue($player, (string) $req->stat_key),
            MagicSkillRequirementType::LEVEL => (int) $player->lvl,
        };

        return [
            'label' => $req->label(),
            'required' => (int) $req->min_value,
            'current' => $current,
            'met' => $current >= (int) $req->min_value,
        ];
    }

    private function statValue(Player $player, string $key): int
    {
        return match (PlayerStatKey::tryFrom($key)) {
            PlayerStatKey::STRENGTH => (int) floor($player->strength),
            PlayerStatKey::AGILITY => (int) floor($player->agility),
            PlayerStatKey::INTUITION => (int) floor($player->intuition),
            PlayerStatKey::WISDOM => (int) floor($player->wisdom),
            PlayerStatKey::INTELLIGENCE => (int) floor($player->intelligence),
            PlayerStatKey::ENDURANCE => (int) floor($player->endurance),
            null => 0,
        };
    }
}
