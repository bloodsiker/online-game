<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Clan\Application\DTOs\ClanSkillsPageDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanSkillsPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(User $user): ClanSkillsPageDTO
    {
        $context = $this->resolveClanContext->require($user);

        $definitions = ClanSkillDefinition::with(['levels.itemRequirements.shareItem', 'levels.stoneItem', 'levels.magicSkill'])
            ->orderBy('sort_order')
            ->get();

        $learnedMap = $context->clan->learnedSkills()
            ->pluck('current_level', 'clan_skill_definition_id');

        $player = $user->player;
        $backpackShareItemCounts = Backpack::where('user_id', $user->id)
            ->with('item')
            ->get()
            ->groupBy(fn ($backpack) => $backpack->item->share_item_id)
            ->map(fn ($backpacks) => $backpacks->sum('count'));

        return new ClanSkillsPageDTO(
            clan: $context->clan,
            membership: $context->membership,
            definitions: $definitions,
            learnedMap: $learnedMap,
            canLearn: $context->membership->role->hasPermission(ClanPermission::LEARN_SKILL),
            backpackShareItemCounts: $backpackShareItemCounts,
            player: $player,
            playerDecorator: $this->statService->resolve($player),
        );
    }
}
