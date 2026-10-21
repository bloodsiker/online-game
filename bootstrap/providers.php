<?php

use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Backpack\BackpackServiceProvider;
use App\Modules\Battle\BattleServiceProvider;
use App\Modules\Chat\ChatServiceProvider;
use App\Modules\Clan\ClanServiceProvider;
use App\Modules\Dungeon\DungeonServiceProvider;
use App\Modules\Effect\EffectServiceProvider;
use App\Modules\Event\EventServiceProvider;
use App\Modules\Forum\ForumServiceProvider;
use App\Modules\Friend\FriendServiceProvider;
use App\Modules\Influence\InfluenceServiceProvider;
use App\Modules\Interface\InterfaceServiceProvider;
use App\Modules\Item\ItemServiceProvider;
use App\Modules\Library\LibraryServiceProvider;
use App\Modules\Location\LocationServiceProvider;
use App\Modules\MagicSkill\MagicSkillServiceProvider;
use App\Modules\Monster\MonsterServiceProvider;
use App\Modules\News\NewsServiceProvider;
use App\Modules\Notification\NotificationServiceProvider;
use App\Modules\Npc\NpcServiceProvider;
use App\Modules\Party\PartyServiceProvider;
use App\Modules\Player\PlayerServiceProvider;
use App\Modules\PlayerTrade\PlayerTradeServiceProvider;
use App\Modules\Post\PostServiceProvider;
use App\Modules\Quest\QuestServiceProvider;
use App\Modules\Race\RaceServiceProvider;
use App\Modules\Rating\RatingServiceProvider;
use App\Modules\Referral\ReferralServiceProvider;
use App\Modules\Reputation\ReputationServiceProvider;
use App\Modules\Share\ShareServiceProvider;
use App\Modules\Structure\StructureServiceProvider;
use App\Modules\Trade\TradeServiceProvider;
use App\Modules\User\UserServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    BattleServiceProvider::class,
    ChatServiceProvider::class,
    DungeonServiceProvider::class,
    EffectServiceProvider::class,
    FriendServiceProvider::class,
    ForumServiceProvider::class,
    InterfaceServiceProvider::class,
    InfluenceServiceProvider::class,
    ItemServiceProvider::class,
    LibraryServiceProvider::class,
    LocationServiceProvider::class,
    MagicSkillServiceProvider::class,
    MonsterServiceProvider::class,
    NewsServiceProvider::class,
    NotificationServiceProvider::class,
    NpcServiceProvider::class,
    PartyServiceProvider::class,
    PlayerServiceProvider::class,
    PlayerTradeServiceProvider::class,
    QuestServiceProvider::class,
    RaceServiceProvider::class,
    RatingServiceProvider::class,
    ReputationServiceProvider::class,
    ShareServiceProvider::class,
    UserServiceProvider::class,
    ReferralServiceProvider::class,
    StructureServiceProvider::class,
    TradeServiceProvider::class,
    ClanServiceProvider::class,
    BackpackServiceProvider::class,
    EventServiceProvider::class,
    PostServiceProvider::class,
];
