<?php

declare(strict_types=1);

namespace App\Modules\Npc\Application\DTOs;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Collection;

final readonly class NpcPageDTO
{
    public function __construct(
        public Npc $npc,
        public Collection $quests,
        public Collection $questsInProgress,
        public Collection $questsOnCooldown,
        public ?string $message,
        public string $messageType,
        public Player $player,
        public ?NpcDialogueNode $dialogueStartNode = null,
        public Collection $reputationShops = new Collection,
        public bool $isClanRegistrar = false,
        public bool $hasClanMembership = false,
    ) {}
}
