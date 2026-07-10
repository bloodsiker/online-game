<?php

use App\Modules\Backpack\BackpackServiceProvider;
use App\Modules\Battle\BattleServiceProvider;
use App\Modules\Chat\ChatServiceProvider;
use App\Modules\Clan\ClanServiceProvider;
use App\Modules\Dungeon\DungeonServiceProvider;
use App\Modules\Event\EventServiceProvider;
use App\Modules\Friend\FriendServiceProvider;
use App\Modules\Interface\InterfaceServiceProvider;
use App\Modules\Item\ItemServiceProvider;
use App\Modules\Location\LocationServiceProvider;
use App\Modules\MagicSkill\MagicSkillServiceProvider;
use App\Modules\Monster\MonsterServiceProvider;
use App\Modules\Npc\NpcServiceProvider;
use App\Modules\Player\PlayerServiceProvider;
use App\Modules\Post\PostServiceProvider;
use App\Modules\Quest\QuestServiceProvider;
use App\Modules\Race\RaceServiceProvider;
use App\Modules\Rating\RatingServiceProvider;
use App\Modules\Referral\ReferralServiceProvider;
use App\Modules\Reputation\ReputationServiceProvider;
use App\Modules\Share\ShareServiceProvider;
use App\Modules\Structure\StructureServiceProvider;
use App\Modules\User\UserServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    BattleServiceProvider::class,
    ChatServiceProvider::class,
    DungeonServiceProvider::class,
    FriendServiceProvider::class,
    InterfaceServiceProvider::class,
    ItemServiceProvider::class,
    LocationServiceProvider::class,
    MagicSkillServiceProvider::class,
    MonsterServiceProvider::class,
    NpcServiceProvider::class,
    PlayerServiceProvider::class,
    QuestServiceProvider::class,
    RaceServiceProvider::class,
    RatingServiceProvider::class,
    ReputationServiceProvider::class,
    ShareServiceProvider::class,
    UserServiceProvider::class,
    ReferralServiceProvider::class,
    StructureServiceProvider::class,
    ClanServiceProvider::class,
    BackpackServiceProvider::class,
    EventServiceProvider::class,
    PostServiceProvider::class,
];
