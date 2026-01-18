<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Auction{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $structure_id
 * @property int $item_id
 * @property int $count
 * @property int $price
 * @property int $is_anonymous
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item $item
 * @property-read \App\Models\Structure|null $structure
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereIsAnonymous($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereUserId($value)
 */
	class Auction extends \Eloquent {}
}

namespace App\Models\Auction{
/**
 * @property int $id
 * @property int $buy_user_id
 * @property int $sell_user_id
 * @property int|null $structure_id
 * @property int $item_id
 * @property int $count
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $buyUser
 * @property-read \App\Models\Item\Item $item
 * @property-read \App\Models\User $sellUser
 * @property-read \App\Models\Structure|null $structure
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereBuyUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereSellUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuctionHistory whereUpdatedAt($value)
 */
	class AuctionHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $user_id
 * @property int $item_id
 * @property bool $equipped
 * @property int $count
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item $item
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereEquipped($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backpack whereUserId($value)
 */
	class Backpack extends \Eloquent {}
}

namespace App\Models\Battle{
/**
 * @property int $id
 * @property int $location_id
 * @property int $rounds
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Battle\BattleDetail> $details
 * @property-read int|null $details_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Battle\BattleDetail> $detailsWithMonsters
 * @property-read int|null $details_with_monsters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Battle\BattleDetail> $detailsWithUsers
 * @property-read int|null $details_with_users_count
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereRounds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Battle whereUpdatedAt($value)
 */
	class Battle extends \Eloquent {}
}

namespace App\Models\Battle{
/**
 * @property int $id
 * @property int $battle_id
 * @property int|null $user_id
 * @property int|null $location_monster_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Monster\MonsterOnLocation|null $locationMonster
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereBattleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereLocationMonsterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleDetail whereUserId($value)
 */
	class BattleDetail extends \Eloquent {}
}

namespace App\Models\Battle{
/**
 * @property int $id
 * @property int $battle_id
 * @property int|null $user_id
 * @property int|null $location_monster_id
 * @property int $round_number
 * @property string $action
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Monster\MonsterOnLocation|null $locationMonster
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereBattleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereLocationMonsterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereRoundNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BattleRound whereUserId($value)
 */
	class BattleRound extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $structure_id
 * @property int $from_share_item_id
 * @property int $to_share_item_id
 * @property int $from_amount
 * @property int $to_amount
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShareItem $fromItem
 * @property-read \App\Models\Structure $structure
 * @property-read \App\Models\ShareItem $toItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereFromAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereFromShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereToAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereToShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exchange whereUpdatedAt($value)
 */
	class Exchange extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $lvl
 * @property int $exp
 * @property int $exp_diff
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereExpDiff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereUpdatedAt($value)
 */
	class Experience extends \Eloquent {}
}

namespace App\Models\Item{
/**
 * @property int $upgrade_lvl
 * @property int $share_item_id
 * @property int $additional_attack
 * @property int $count_use
 * @property bool $is_open
 * @property int $count
 * @property-read ShareItem $itemInfo
 * @property-read Collection|Item[] $itemsInChest
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $items_in_chest_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereAdditionalAttack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCountUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUpgradeLvl($value)
 */
	class Item extends \Eloquent {}
}

namespace App\Models\Item{
/**
 * @property int $id
 * @property int $chest_id
 * @property int $item_id
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item $chest
 * @property-read \App\Models\Item\Item $item
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereChestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemInChest whereUpdatedAt($value)
 */
	class ItemInChest extends \Eloquent {}
}

namespace App\Models\Item{
/**
 * @property int $id
 * @property int $item_id
 * @property int $location_id
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item $item
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemOnLocation whereUpdatedAt($value)
 */
	class ItemOnLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $map_id
 * @property string $name
 * @property string|null $description
 * @property int|null $north
 * @property int|null $south
 * @property int|null $east
 * @property int|null $west
 * @property int|null $up
 * @property int|null $down
 * @property int $count_monster
 * @property int $percent_respawn_monster
 * @property int $time_not_attack
 * @property \Illuminate\Support\Carbon|null $last_respawn_monster_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShareAction> $actions
 * @property-read int|null $actions_count
 * @property-read Location|null $downSide
 * @property-read Location|null $eastSide
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item\Item> $itemsOnLocation
 * @property-read int|null $items_on_location_count
 * @property-read \App\Models\Map $map
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Monster\Monster> $monsters
 * @property-read int|null $monsters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Monster\Monster> $monstersOnLocation
 * @property-read int|null $monsters_on_location_count
 * @property-read Location|null $northSide
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Npc> $npcs
 * @property-read int|null $npcs_count
 * @property-read Location|null $southSide
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Structure> $structures
 * @property-read int|null $structures_count
 * @property-read Location|null $upSide
 * @property-read Location|null $westSide
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCountMonster($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereDown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereEast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLastRespawnMonsterAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereNorth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location wherePercentRespawnMonster($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereSouth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereTimeNotAttack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereWest($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models\MagicSkill{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $description
 * @property int $chance
 * @property int $duration
 * @property bool $is_stackable
 * @property int $max_stacks
 * @property int $tick_interval
 * @property int|null $value_per_tick
 * @property array<array-key, mixed>|null $stat_modifiers
 * @property bool $is_dispellable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MagicSkill\MagicSkill> $magicSkills
 * @property-read int|null $magic_skills_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player\PlayerEffect> $players
 * @property-read int|null $players_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereChance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereIsDispellable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereIsStackable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereMaxStacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereStatModifiers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereTickInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Effect whereValuePerTick($value)
 */
	class Effect extends \Eloquent {}
}

namespace App\Models\MagicSkill{
/**
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $level
 * @property string $type
 * @property int $mana_cost
 * @property int $min_damage
 * @property int $max_damage
 * @property int $base_healing
 * @property int $cooldown
 * @property string $target_type
 * @property bool $is_passive
 * @property array $effects
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player\PlayerEffect> $appliedEffects
 * @property-read int|null $applied_effects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player\Player> $players
 * @property-read int|null $players_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MagicSkill\Effect> $skillEffects
 * @property-read int|null $skill_effects_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereBaseHealing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereCooldown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereEffects($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereIsPassive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereManaCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereMaxDamage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereMinDamage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkill whereUpdatedAt($value)
 */
	class MagicSkill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $folder
 * @property string|null $slug
 * @property int $resp_location_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Map> $children
 * @property-read int|null $children_count
 * @property-read Map|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereRespLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Map whereUpdatedAt($value)
 */
	class Map extends \Eloquent {}
}

namespace App\Models\Monster{
/**
 * @property int $id
 * @property string $name
 * @property int $lvl
 * @property int $hp
 * @property int $armor
 * @property int $dodge
 * @property int $critical
 * @property float $min_dmg
 * @property float $max_dmg
 * @property int $aggression
 * @property int $exp
 * @property int $min_money
 * @property int $max_money
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MagicSkill\Effect> $effects
 * @property-read int|null $effects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShareItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $locations
 * @property-read int|null $locations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereAggression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereArmor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereCritical($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereDodge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereMaxDmg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereMaxMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereMinDmg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereMinMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Monster whereUpdatedAt($value)
 */
	class Monster extends \Eloquent implements \App\Services\Combat\FightHitInterface {}
}

namespace App\Models\Monster{
/**
 * @property-read \App\Models\MagicSkill\Effect|null $effect
 * @property-read \App\Models\Monster\Monster|null $monster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterActiveEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterActiveEffect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterActiveEffect query()
 */
	class MonsterActiveEffect extends \Eloquent {}
}

namespace App\Models\Monster{
/**
 * @property int $id
 * @property int $monster_id
 * @property int $effect_id
 * @property int $chance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereChance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereEffectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereMonsterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterEffect whereUpdatedAt($value)
 */
	class MonsterEffect extends \Eloquent {}
}

namespace App\Models\Monster{
/**
 * @property int $id
 * @property int $monster_id
 * @property int $location_id
 * @property int $hp_now
 * @property int $hp_max
 * @property int $active
 * @property int $is_drop_money
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\Monster\Monster $monster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereHpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereHpNow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereIsDropMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereMonsterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonsterOnLocation whereUpdatedAt($value)
 */
	class MonsterOnLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $location_id
 * @property string $name
 * @property string $description
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quest\Quest> $completeQuests
 * @property-read int|null $complete_quests_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quest\Quest> $startQuests
 * @property-read int|null $start_quests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Structure> $structures
 * @property-read int|null $structures_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Npc whereUpdatedAt($value)
 */
	class Npc extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property int $id
 * @property int $user_id
 * @property int $race_id
 * @property int $lvl
 * @property int $exp
 * @property int $exp_up
 * @property int $exp_diff
 * @property float $str
 * @property float $agil
 * @property float $int
 * @property float $mud
 * @property float $intel
 * @property int $hp_now
 * @property int $hp_max
 * @property int $mp_now
 * @property int $mp_max
 * @property float $min_dmg
 * @property float $max_dmg
 * @property int $dodge
 * @property int $critical
 * @property int $free_stats
 * @property int $victory
 * @property int $death
 * @property int $is_main
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $last_regen_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MagicSkill\MagicSkill> $magicSkills
 * @property-read int|null $magic_skills_count
 * @property-read \App\Models\Player\PlayerEquipment|null $playerEquip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quest\QuestPlayer> $quests
 * @property-read int|null $quests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quest\QuestPlayer> $questsInProgress
 * @property-read int|null $quests_in_progress_count
 * @property-read \App\Models\Race $race
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player\PlayerSkill> $skills
 * @property-read int|null $skills_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereAgil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCritical($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDeath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereDodge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereExpDiff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereExpUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereFreeStats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereHpNow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereInt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereIntel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereIsMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereLastRegenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereMaxDmg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereMinDmg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereMpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereMpNow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereMud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereRaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereVictory($value)
 */
	class Player extends \Eloquent implements \App\Decorator\Player\PlayerInterface, \App\Services\Combat\FightHitInterface {}
}

namespace App\Models\Player{
/**
 * @property int $id
 * @property int $player_id
 * @property int $effect_id
 * @property int|null $source_player_id
 * @property int|null $source_magic_skill_id
 * @property \Illuminate\Support\Carbon $applied_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property int $stacks
 * @property int|null $current_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MagicSkill\Effect $effect
 * @property-read \App\Models\Player\Player $player
 * @property-read \App\Models\MagicSkill\MagicSkill|null $sourceMagicSkill
 * @property-read \App\Models\Player\Player|null $sourcePlayer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereAppliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereEffectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereSourceMagicSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereSourcePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereStacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerActiveEffect whereUpdatedAt($value)
 */
	class PlayerActiveEffect extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property-read \App\Models\MagicSkill\Effect|null $effect
 * @property-read \App\Models\Player\Player|null $player
 * @property-read \App\Models\MagicSkill\MagicSkill|null $sourceMagicSkill
 * @property-read \App\Models\Player\Player|null $sourcePlayer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEffect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEffect query()
 */
	class PlayerEffect extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property int $id
 * @property int $player_id
 * @property int|null $hand_left Левая рука
 * @property int|null $hand_right Правая рука
 * @property int|null $helmet Шлем
 * @property int|null $shoulder Наплечники
 * @property int|null $forearm Наручии
 * @property int|null $armor Броня
 * @property int|null $legging Поножи
 * @property int|null $chain_armor Кольчуга
 * @property int|null $cloak Накидка
 * @property int|null $shoes Обувь
 * @property int|null $gloves Перчатки
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item|null $armorSlot
 * @property-read \App\Models\Item\Item|null $chainArmorSlot
 * @property-read \App\Models\Item\Item|null $cloakSlot
 * @property-read \App\Models\Item\Item|null $forearmSlot
 * @property-read \App\Models\Item\Item|null $glovesSlot
 * @property-read \App\Models\Item\Item|null $handLeft
 * @property-read \App\Models\Item\Item|null $handRight
 * @property-read \App\Models\Item\Item|null $helmetSlot
 * @property-read \App\Models\Item\Item|null $leggingSlot
 * @property-read \App\Models\Player\Player $player
 * @property-read \App\Models\Item\Item|null $shoesSlot
 * @property-read \App\Models\Item\Item|null $shoulderSlot
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereArmor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereChainArmor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereForearm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereGloves($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereHandLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereHandRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereLegging($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereShoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereShoulder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerEquipment whereUpdatedAt($value)
 */
	class PlayerEquipment extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property int $id
 * @property int $player_id
 * @property int $magic_skill_id
 * @property \Illuminate\Support\Carbon|null $cooldown_end_at
 * @property bool $is_equipped
 * @property-read \App\Models\MagicSkill\MagicSkill $magicSkill
 * @property-read \App\Models\Player\Player $player
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill whereCooldownEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill whereIsEquipped($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill whereMagicSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMagicSkill wherePlayerId($value)
 */
	class PlayerMagicSkill extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property int $id
 * @property int|null $player_id
 * @property int|null $skill_id
 * @property int $lvl
 * @property int $exp
 * @property int $exp_up
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Player\Player|null $player
 * @property-read \App\Models\Skill|null $skill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereExpUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSkill whereUpdatedAt($value)
 */
	class PlayerSkill extends \Eloquent {}
}

namespace App\Models\Player{
/**
 * @property-read \App\Models\Item\Item|null $item
 * @property-read \App\Models\Player\Player|null $player
 * @property-read \App\Models\Player\PlayerMagicSkill|null $skill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSlot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSlot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerSlot query()
 */
	class PlayerSlot extends \Eloquent {}
}

namespace App\Models\Quest{
/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \App\Enums\QuestType $type
 * @property int $start_npc_id
 * @property int $complete_npc_id
 * @property int|null $parent_quest_id
 * @property int|null $after_quest_id
 * @property int|null $reset_period
 * @property bool $is_active
 * @property bool $is_finish
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Quest|null $afterQuest
 * @property-read \App\Models\Npc $completeNpc
 * @property-read \App\Models\Quest\QuestObjective|null $objective
 * @property-read Quest|null $parentQuest
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Quest\QuestReward> $rewards
 * @property-read int|null $rewards_count
 * @property-read \App\Models\Npc $startNpc
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest isActive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereAfterQuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereCompleteNpcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereIsFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereParentQuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereResetPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereStartNpcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quest whereUpdatedAt($value)
 */
	class Quest extends \Eloquent {}
}

namespace App\Models\Quest{
/**
 * @property int $id
 * @property int $quest_id
 * @property string $type
 * @property string $target_type
 * @property int $target_id
 * @property int|null $required_amount
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Quest\Quest $quest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereQuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereRequiredAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestObjective whereUpdatedAt($value)
 */
	class QuestObjective extends \Eloquent {}
}

namespace App\Models\Quest{
/**
 * @property int $id
 * @property int $player_id
 * @property int $quest_id
 * @property \App\Enums\QuestPlayerStatus $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $reset_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Quest\QuestPlayerObjective|null $objective
 * @property-read \App\Models\Player\Player $player
 * @property-read \App\Models\Quest\Quest $quest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereQuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereResetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayer whereUpdatedAt($value)
 */
	class QuestPlayer extends \Eloquent {}
}

namespace App\Models\Quest{
/**
 * @property int $id
 * @property int $quest_player_id
 * @property int $quest_objective_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Quest\QuestObjective $questObjective
 * @property-read \App\Models\Quest\QuestPlayer $questPlayer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereQuestObjectiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereQuestPlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestPlayerObjective whereUpdatedAt($value)
 */
	class QuestPlayerObjective extends \Eloquent {}
}

namespace App\Models\Quest{
/**
 * @property int $id
 * @property int $quest_id
 * @property string $type
 * @property int $amount
 * @property int|null $share_item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShareItem|null $itemInfo
 * @property-read \App\Models\Quest\Quest $quest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereQuestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuestReward whereUpdatedAt($value)
 */
	class QuestReward extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property float $str
 * @property float $agil
 * @property float $int
 * @property float $mud
 * @property float $intel
 * @property int $free_stats
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereAgil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereFreeStats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereInt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereIntel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereMud($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Race whereUpdatedAt($value)
 */
	class Race extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Structure> $structures
 * @property-read int|null $structures_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareAction whereUpdatedAt($value)
 */
	class ShareAction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property int $is_two_hand
 * @property int $min_attack
 * @property int $max_attack
 * @property int $armor
 * @property int $count_use
 * @property bool $is_heal
 * @property bool $is_active
 * @property bool $is_sell
 * @property int $price
 * @property int $break_crystal
 * @property string|null $slot
 * @property int|null $skill_id
 * @property int|null $skill_lvl
 * @property int|null $skill_exp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ShareItem> $itemHasItems
 * @property-read int|null $item_has_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Monster\Monster> $monsters
 * @property-read int|null $monsters_count
 * @property-read \App\Models\ShareRecipe|null $recipe
 * @property-read \App\Models\Skill|null $skill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem byGroup(string $group)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereArmor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereBreakCrystal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereCountUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereIsHeal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereIsSell($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereIsTwoHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereMaxAttack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereMinAttack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereSkillExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereSkillLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereSlot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareItem whereUpdatedAt($value)
 */
	class ShareItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $share_item_id
 * @property int $kraft_item_id
 * @property int $percent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShareItem $itemInfo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShareItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\ShareItem $kraftItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe whereKraftItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe whereShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareRecipe whereUpdatedAt($value)
 */
	class ShareRecipe extends \Eloquent {}
}

namespace App\Models\Share{
/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShareStructureCategory whereUpdatedAt($value)
 */
	class ShareStructureCategory extends \Eloquent {}
}

namespace App\Models\Shop{
/**
 * @property int $id
 * @property int $user_id
 * @property int $shop_item_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Shop\ShopItem $shopItem
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereShopItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart whereUserId($value)
 */
	class ShopCart extends \Eloquent {}
}

namespace App\Models\Shop{
/**
 * @property int $id
 * @property int $structure_id
 * @property int $share_item_id
 * @property int|null $share_structure_category_id
 * @property int $price
 * @property int $diamond
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Share\ShareStructureCategory|null $category
 * @property-read \App\Models\ShareItem $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop\ShopItemRequirement> $requirements
 * @property-read int|null $requirements_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereShareStructureCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItem whereUpdatedAt($value)
 */
	class ShopItem extends \Eloquent {}
}

namespace App\Models\Shop{
/**
 * @property int $id
 * @property int $shop_item_id
 * @property int $share_item_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShareItem $item
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereShareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereShopItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopItemRequirement whereUpdatedAt($value)
 */
	class ShopItemRequirement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereUpdatedAt($value)
 */
	class Skill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property int|null $location_id
 * @property int|null $npc_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShareAction> $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Share\ShareStructureCategory> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exchange> $exchangeItems
 * @property-read int|null $exchange_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShareItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Npc|null $npc
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop\ShopItem> $shopItems
 * @property-read int|null $shop_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereNpcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Structure whereUpdatedAt($value)
 */
	class Structure extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $player_id
 * @property int|null $location_id
 * @property int|null $prev_location_id
 * @property int $is_admin
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $last_online_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $money
 * @property int $diamond
 * @property int $warehouse_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Backpack> $backpack
 * @property-read int|null $backpack_count
 * @property-read \app\Models\Clan\ClanMember|null $clanMembership
 * @property-read \App\Models\Location|null $currentLocation
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Player\Player|null $player
 * @property-read \App\Models\Location|null $prevLocation
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastOnlineAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrevLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWarehouseCount($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $structure_id
 * @property int|null $item_id
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item\Item|null $item
 * @property-read \App\Models\Structure|null $structure
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereUserId($value)
 */
	class Warehouse extends \Eloquent {}
}

namespace app\Models\Clan{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $lvl
 * @property string|null $icon
 * @property int $owner_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\Models\Clan\ClanMember> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereLvl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clan whereUpdatedAt($value)
 */
	class Clan extends \Eloquent {}
}

namespace app\Models\Clan{
/**
 * @property int $id
 * @property int $clan_id
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \app\Models\Clan\Clan $clan
 * @property-read \app\Models\Clan\ClanRole $role
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereClanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanMember whereUserId($value)
 */
	class ClanMember extends \Eloquent {}
}

namespace app\Models\Clan{
/**
 * @property int $id
 * @property string $name
 * @property string $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClanRole whereUpdatedAt($value)
 */
	class ClanRole extends \Eloquent {}
}

namespace app\Models{
/**
 * @property int $id
 * @property int $location_id
 * @property int $monster_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationHasMonster whereMonsterId($value)
 */
	class LocationHasMonster extends \Eloquent {}
}

namespace app\Models{
/**
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Monster\Monster|null $monster
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationMonster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationMonster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationMonster query()
 */
	class LocationMonster extends \Eloquent {}
}

namespace app\Models\MagicSkill{
/**
 * @property int $id
 * @property int $magic_skill_id
 * @property int $effect_id
 * @property int $chance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereChance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereEffectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereMagicSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MagicSkillEffect whereUpdatedAt($value)
 */
	class MagicSkillEffect extends \Eloquent {}
}

