<?php

namespace App\Console\Commands;

use App\Enums\BossMechanicType;
use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use App\Enums\QuestType;
use App\Enums\ShareItemSlot;
use App\Enums\ShareItemStatType;
use App\Enums\ShareItemType;
use App\Models\Exchange;
use App\Models\Experience;
use App\Models\MagicSkill\Effect;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Monster\BossMechanic;
use App\Models\Monster\BossPhase;
use App\Models\Monster\Monster;
use App\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Models\Race;
use App\Models\Share\ShareAction;
use App\Models\Share\ShareItem;
use App\Models\Share\ShareItemEffect;
use App\Models\Share\ShareRecipe;
use App\Models\Share\ShareStructureCategory;
use App\Models\Skill;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class GenerateSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $location1 = null;

    protected $location2 = null;

    protected $location3 = null;

    protected $defaultRace = null;

    protected $user1 = null;

    protected $monster1 = null;

    protected $monster2 = null;

    protected $boss = null;

    protected $boss2 = null;

    protected $boss3 = null;

    protected $boss4 = null;

    protected $boss5 = null;

    protected $skill = null;

    protected $skill2 = null;

    protected $shopCategory1 = null;

    protected $shopCategory2 = null;

    protected $shopCategory3 = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->createExp();
        $this->createLocation();
        $this->createRace();
        $this->createUser();
        $this->createMonster();
        $this->createBoss();
        $this->createSkillAndEffects();
        $this->createLocationHasMonsters();
        $this->createSkills();
        $this->createItems();
        $this->createRecipeItems();
        $this->createBoxItems();
        $this->createShareActions();
        $this->createShareShopCategory();
        $this->createStructures();
        $this->createNpcAndQuest();
        $this->call('db:seed', ['--class' => 'ClanSkillSeeder']);
        $this->call('db:seed', ['--class' => 'QuestSeeder']);
        $this->call('db:seed', ['--class' => 'ClanQuestSeeder']);
        $this->call('db:seed', ['--class' => 'ReputationSeeder']);
        $this->call('db:seed', ['--class' => 'DungeonSeeder']);
        $this->call('db:seed', ['--class' => 'BuffSkillSeeder']);
    }

    public function createExp()
    {
        DB::table('experiences')->insert([
            ['lvl' => 1, 'exp' => 0, 'exp_diff' => 100],
            ['lvl' => 2, 'exp' => 100, 'exp_diff' => 200],
            ['lvl' => 3, 'exp' => 300, 'exp_diff' => 300],
            ['lvl' => 4, 'exp' => 600, 'exp_diff' => 400],
            ['lvl' => 5, 'exp' => 1000, 'exp_diff' => 500],
            ['lvl' => 6, 'exp' => 1500, 'exp_diff' => 1000],
            ['lvl' => 7, 'exp' => 2500, 'exp_diff' => 1500],
            ['lvl' => 8, 'exp' => 4000, 'exp_diff' => 2500],
            ['lvl' => 9, 'exp' => 6500, 'exp_diff' => 4000],
            ['lvl' => 10, 'exp' => 10500, 'exp_diff' => 6000],
            ['lvl' => 11, 'exp' => 16500, 'exp_diff' => 7000],
            ['lvl' => 12, 'exp' => 23500, 'exp_diff' => 8000],
            ['lvl' => 13, 'exp' => 31500, 'exp_diff' => 9000],
            ['lvl' => 14, 'exp' => 40500, 'exp_diff' => 10000],
            ['lvl' => 15, 'exp' => 50500, 'exp_diff' => 12000],
            ['lvl' => 16, 'exp' => 62500, 'exp_diff' => 14000],
            ['lvl' => 17, 'exp' => 76500, 'exp_diff' => 16000],
            ['lvl' => 18, 'exp' => 92500, 'exp_diff' => 18000],
            ['lvl' => 19, 'exp' => 110500, 'exp_diff' => 20000],
            ['lvl' => 20, 'exp' => 130500, 'exp_diff' => 23000],
            ['lvl' => 21, 'exp' => 153500, 'exp_diff' => 26000],
            ['lvl' => 22, 'exp' => 179500, 'exp_diff' => 29000],
            ['lvl' => 23, 'exp' => 208500, 'exp_diff' => 32000],
            ['lvl' => 24, 'exp' => 240500, 'exp_diff' => 35000],
            ['lvl' => 25, 'exp' => 275500, 'exp_diff' => 38000],
            ['lvl' => 26, 'exp' => 313500, 'exp_diff' => 42000],
            ['lvl' => 27, 'exp' => 355500, 'exp_diff' => 46000],
            ['lvl' => 28, 'exp' => 401500, 'exp_diff' => 50000],
            ['lvl' => 29, 'exp' => 451500, 'exp_diff' => 55000],
            ['lvl' => 30, 'exp' => 506500, 'exp_diff' => 60000],
            ['lvl' => 31, 'exp' => 566500, 'exp_diff' => 66000],
            ['lvl' => 32, 'exp' => 632500, 'exp_diff' => 72000],
            ['lvl' => 33, 'exp' => 704500, 'exp_diff' => 78000],
            ['lvl' => 34, 'exp' => 782500, 'exp_diff' => 85000],
            ['lvl' => 35, 'exp' => 867500, 'exp_diff' => 92000],
            ['lvl' => 36, 'exp' => 959500, 'exp_diff' => 100000],
            ['lvl' => 37, 'exp' => 1059500, 'exp_diff' => 110000],
            ['lvl' => 38, 'exp' => 1169500, 'exp_diff' => 120000],
            ['lvl' => 39, 'exp' => 1289500, 'exp_diff' => 130000],
            ['lvl' => 40, 'exp' => 1419500, 'exp_diff' => 145000],
            ['lvl' => 41, 'exp' => 1564500, 'exp_diff' => 160000],
            ['lvl' => 42, 'exp' => 1724500, 'exp_diff' => 175000],
            ['lvl' => 43, 'exp' => 1899500, 'exp_diff' => 190000],
            ['lvl' => 44, 'exp' => 2089500, 'exp_diff' => 205000],
            ['lvl' => 45, 'exp' => 2294500, 'exp_diff' => 225000],
            ['lvl' => 46, 'exp' => 2519500, 'exp_diff' => 245000],
            ['lvl' => 47, 'exp' => 2764500, 'exp_diff' => 265000],
            ['lvl' => 48, 'exp' => 3029500, 'exp_diff' => 290000],
            ['lvl' => 49, 'exp' => 3319500, 'exp_diff' => 320000],
            ['lvl' => 50, 'exp' => 3639500, 'exp_diff' => 350000],
        ]);
    }

    protected function createLocation()
    {
        $sql = File::get(public_path('dumpSql/location.sql'));
        DB::unprepared($sql);

        $this->location1 = Location::find(1);
        $this->location2 = Location::find(2);
        $this->location3 = Location::find(3);

        $this->info('Create Locations success');
    }

    protected function createRace()
    {
        $race = new Race;
        $race->name = 'Человек';
        $race->strength = 1;
        $race->agility = 1;
        $race->intuition = 1;
        $race->wisdom = 1;
        $race->intelligence = 1;
        $race->free_stats = 5;
        $race->save();

        $this->defaultRace = $race;

        $race = new Race;
        $race->name = 'Эльф';
        $race->strength = 0.7;
        $race->agility = 0.3;
        $race->intuition = 0.2;
        $race->wisdom = 0.8;
        $race->intelligence = 3;
        $race->free_stats = 5;
        $race->save();

        $race = new Race;
        $race->name = 'Темный эльф';
        $race->strength = 0.7;
        $race->agility = 0.2;
        $race->intuition = 3;
        $race->wisdom = 0.6;
        $race->intelligence = 0.5;
        $race->free_stats = 5;
        $race->save();
        $this->defaultRace = $race;

        $race = new Race;
        $race->name = 'Дварф';
        $race->strength = 3;
        $race->agility = 0.5;
        $race->intuition = 0.5;
        $race->wisdom = 0.5;
        $race->intelligence = 0.5;
        $race->free_stats = 5;
        $race->save();

        $race = new Race;
        $race->name = 'Хоббит';
        $race->strength = 0.7;
        $race->agility = 3;
        $race->intuition = 0.2;
        $race->wisdom = 0.5;
        $race->intelligence = 0.6;
        $race->free_stats = 5;
        $race->save();

        $this->info('Create Races success');
    }

    protected function createUser()
    {
        $user = new User;
        $user->is_admin = true;
        $user->name = 'Tap0K';
        $user->password = Hash::make('q1w2q1w2');
        $user->email = 'maldini2@ukr.net';
        $user->last_online_at = Carbon::now();
        $user->location_id = $this->location1->id;
        $user->save();

        $this->user1 = $user;

        $exp = Experience::where('lvl', 1)->first();

        $player = new Player;
        $player->user_id = $user->id;
        $player->race_id = $this->defaultRace->id;
        $player->lvl = 1;
        $player->exp = 0;
        $player->exp_up = $exp->exp + $exp->exp_diff;
        $player->exp_diff = $exp->exp_diff;
        $player->strength = 1;
        $player->agility = 1;
        $player->intuition = 1;
        $player->wisdom = 1;
        $player->intelligence = 1;
        $player->hp_now = 10;
        $player->hp_max = 10;
        $player->mp_now = 10;
        $player->mp_max = 10;
        $player->min_dmg = 1;
        $player->max_dmg = 2;
        $player->dodge = 0;
        $player->critical = 0;
        $player->free_stats = 5;
        $player->victory = 0;
        $player->death = 0;
        $player->is_main = 1;
        $player->is_active = 1;
        $player->save();

        $equip1 = new PlayerEquipment;
        $equip1->player_id = $player->id;
        $equip1->save();

        $user->player_id = $player->id;
        $user->save();

        $user = new User;
        $user->name = 'BlooDSikeR';
        $user->password = Hash::make('q1w2q1w2');
        $user->email = 'blood@ukr.net';
        $user->last_online_at = Carbon::now();
        $user->location_id = $this->location2->id;
        $user->save();

        $player = new Player;
        $player->user_id = $user->id;
        $player->race_id = $this->defaultRace->id;
        $player->lvl = 1;
        $player->exp = 0;
        $player->exp_up = $exp->exp + $exp->exp_diff;
        $player->exp_diff = $exp->exp_diff;
        $player->strength = 1;
        $player->agility = 1;
        $player->intuition = 1;
        $player->wisdom = 1;
        $player->intelligence = 1;
        $player->hp_now = 10;
        $player->hp_max = 10;
        $player->mp_now = 10;
        $player->mp_max = 10;
        $player->min_dmg = 1;
        $player->max_dmg = 2;
        $player->dodge = 0;
        $player->critical = 0;
        $player->free_stats = 5;
        $player->victory = 0;
        $player->death = 0;
        $player->is_main = 1;
        $player->is_active = 1;
        $player->save();

        $equip2 = new PlayerEquipment;
        $equip2->player_id = $player->id;
        $equip2->save();

        $user->player_id = $player->id;
        $user->save();

        $this->info('Create Users success');
        $this->info('Create Player success');
    }

    protected function createSkillAndEffects()
    {
        $skill = new MagicSkill;
        $skill->name = 'Огненный шар';
        $skill->slug = 'fireball';
        $skill->description = 'Огненный шар';
        $skill->level = 1;
        $skill->type = 'attack';
        $skill->mana_cost = 5;
        $skill->min_damage = 3;
        $skill->max_damage = 5;
        $skill->base_healing = 0;
        $skill->cooldown = 0;
        $skill->target_type = 'enemy';
        $skill->is_passive = false;
        $skill->effects = null;
        $skill->save();

        $skill2 = new MagicSkill;
        $skill2->name = 'Ледяная стрела';
        $skill2->slug = 'ice_arrow';
        $skill2->description = 'Ледяная стрела';
        $skill2->level = 1;
        $skill2->type = 'attack';
        $skill2->mana_cost = 10;
        $skill2->min_damage = 5;
        $skill2->max_damage = 8;
        $skill2->base_healing = 0;
        $skill2->cooldown = 0;
        $skill2->target_type = 'enemy';
        $skill2->is_passive = false;
        $skill2->effects = null;
        $skill2->save();

        $skill3 = new MagicSkill;
        $skill3->name = 'Атака ястреба';
        $skill3->slug = 'hawk_attack';
        $skill3->description = 'Атака ястреба';
        $skill3->level = 1;
        $skill3->type = 'utility';
        $skill3->mana_cost = 0;
        $skill3->min_damage = 0;
        $skill3->max_damage = 0;
        $skill3->base_healing = 0;
        $skill3->cooldown = 0;
        $skill3->target_type = 'self';
        $skill3->is_passive = true;
        $skill3->effects = [
            [
                'type' => 'attack',
                'value' => 2,
                'is_percent' => true,
            ],
        ];
        $skill3->save();

        $effect = new Effect;
        $effect->name = 'Ожог';
        $effect->slug = 'burn';
        $effect->type = 'debuff';
        $effect->description = 'Ожог';
        $effect->chance = 50;
        $effect->duration = 10;
        $effect->is_stackable = false;
        $effect->max_stacks = 1;
        $effect->tick_interval = 2;
        $effect->value_per_tick = 2;
        $effect->stat_modifiers = null;
        $effect->is_dispellable = false;
        $effect->save();

        $skill->skillEffects()->attach($effect->id, [
            'chance' => $effect->chance,
        ]);

        $effect2 = new Effect;
        $effect2->name = 'Оглушение';
        $effect2->slug = 'stun';
        $effect2->type = 'debuff';
        $effect2->description = 'Оглушение';
        $effect2->chance = 30;
        $effect2->duration = 30;
        $effect2->is_stackable = true;
        $effect2->max_stacks = 3;
        $effect2->tick_interval = 0;
        $effect2->value_per_tick = 0;
        $effect2->stat_modifiers = null;
        $effect2->is_dispellable = false;
        $effect2->save();

        $effect3 = new Effect;
        $effect3->name = 'Паралич';
        $effect3->slug = 'paralysis';
        $effect3->type = 'debuff';
        $effect3->description = 'Паралич';
        $effect3->chance = 30;
        $effect3->duration = 50;
        $effect3->is_stackable = true;
        $effect3->max_stacks = 3;
        $effect3->tick_interval = 0;
        $effect3->value_per_tick = 0;
        $effect3->stat_modifiers = null;
        $effect3->is_dispellable = true;
        $effect3->save();

        $effect4 = new Effect;
        $effect4->name = 'Атака ястреба';
        $effect4->slug = 'hawk_attack';
        $effect4->type = 'buff';
        $effect4->description = 'С небольшой вероятностью срабатывает в бою и накладывает временное увеличение атаки персонажа';
        $effect4->chance = 5;
        $effect4->duration = 50;
        $effect4->is_stackable = false;
        $effect4->max_stacks = 1;
        $effect4->tick_interval = 0;
        $effect4->value_per_tick = 0;
        $effect4->stat_modifiers = ['attack' => 5];
        $effect4->is_dispellable = false;
        $effect4->save();

        $skill3->skillEffects()->attach($effect4->id, ['chance' => $effect4->chance]);

        $player = $this->user1->player;

        $player->magicSkills()->syncWithoutDetaching([
            $skill->id => [
                'cooldown_end_at' => null,
                'is_equipped' => false,
            ],
        ]);
        $player->magicSkills()->syncWithoutDetaching([
            $skill2->id => [
                'cooldown_end_at' => null,
                'is_equipped' => false,
            ],
        ]);
        $player->magicSkills()->syncWithoutDetaching([
            $skill3->id => [
                'cooldown_end_at' => null,
                'is_equipped' => false,
            ],
        ]);

        $this->monster1->effects()->attach($effect->id, [
            'chance' => $effect->chance,
        ]);
        $this->monster1->effects()->attach($effect2->id, [
            'chance' => $effect2->chance,
        ]);
        $this->monster2->effects()->attach($effect3->id, [
            'chance' => $effect3->chance,
        ]);

        $this->info('Create Skills success');
        $this->info('Create Effects success');
    }

    protected function createMonster()
    {
        $monster = new Monster;
        $monster->name = 'Мышь';
        $monster->lvl = 1;
        $monster->hp = 10;
        $monster->armor = 0;
        $monster->dodge = 0;
        $monster->critical = 0;
        $monster->min_dmg = 1;
        $monster->max_dmg = 2;
        $monster->aggression = 50;
        $monster->min_money = 7;
        $monster->max_money = 12;
        $monster->exp = 40;
        $monster->save();

        $this->monster1 = $monster;

        $monster2 = new Monster;
        $monster2->name = 'Летучая мышь';
        $monster2->lvl = 1;
        $monster2->hp = 15;
        $monster2->armor = 1;
        $monster2->dodge = 1;
        $monster2->critical = 0;
        $monster2->min_dmg = 1;
        $monster2->max_dmg = 3;
        $monster2->aggression = 70;
        $monster2->min_money = 8;
        $monster2->max_money = 14;
        $monster2->exp = 45;
        $monster2->save();

        $this->monster2 = $monster2;

        $this->info('Create Monster success');
    }

    protected function createBoss()
    {
        $boss = new Monster;
        $boss->name = 'Древний дракон';
        $boss->lvl = 30;
        $boss->hp = 5000;
        $boss->armor = 1;
        $boss->dodge = 1;
        $boss->critical = 1;
        $boss->min_dmg = 1;
        $boss->max_dmg = 1;
        $boss->aggression = 0;
        $boss->min_money = 400000;
        $boss->max_money = 500000;
        $boss->exp = 5000;
        $boss->is_boss = true;
        $boss->save();

        $this->boss = $boss;

        // Фаза 1 (100% - 70%)
        BossPhase::create([
            'monster_id' => $boss->id,
            'phase_number' => 1,
            'hp_threshold' => 100,
            'description' => 'Древний дракон спокоен...',
        ]);

        // Фаза 2 (70% - 30%)
        BossPhase::create([
            'monster_id' => $boss->id,
            'phase_number' => 2,
            'hp_threshold' => 70,
            'stats_modifiers' => ['attack' => 30],
            'description' => 'Древний дракон разъярился! Его атака увеличена!',
        ]);

        // Фаза 3 (30% - 0%)
        BossPhase::create([
            'monster_id' => $boss->id,
            'phase_number' => 3,
            'hp_threshold' => 30,
            'stats_modifiers' => ['attack' => 50, 'defence' => -50],
            'description' => 'Древний дракон на грани смерти! Он становится еще опаснее!',
        ]);

        // Механіка: Лють при 50% HP
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::ENRAGE,
            'trigger_hp_percent' => 50,
            'config' => ['damage_increase_percent' => 50, 'one_time' => true],
            'priority' => 100,
        ]);

        // Механіка: Регенерація кожні 5 ходів
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::REGENERATION,
            'trigger_turn' => 5,
            'config' => ['heal_percent' => 5, 'cooldown_turns' => 5],
            'priority' => 50,
        ]);

        // Механіка: Масова атака при 40% HP
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::AOE_ATTACK,
            'trigger_hp_percent' => 40,
            'config' => ['damage_percent' => 40, 'cooldown_turns' => 10],
            'priority' => 70,
        ]);

        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::SHIELD,
            'trigger_turn' => 15,
            'config' => [
                'shield_hp' => 5000,
                'duration_turns' => 3,
                'cooldown_turns' => 15,
            ],
            'priority' => 80,
        ]);

        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::DEATH_EXPLOSION,
            'trigger_hp_percent' => 0, // При смерті
            'config' => [
                'damage_percent' => 30,
            ],
            'priority' => 200,
        ]);

        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::LIFE_DRAIN,
            'trigger_turn' => 5,
            'config' => [
                'drain_percent' => 25,
                'cooldown_turns' => 5,
            ],
            'priority' => 80,
        ]);

        // Відбиття урону при 60% HP
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::REFLECT_DAMAGE,
            'trigger_hp_percent' => 60,
            'config' => [
                'reflect_percent' => 40,
                'duration_turns' => 4,
            ],
            'priority' => 70,
        ]);

        // Імунітет до магії при 40% HP
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::IMMUNITY,
            'trigger_hp_percent' => 40,
            'config' => [
                'immunity_type' => 'magic',
                'duration_turns' => 3,
            ],
            'priority' => 90,
        ]);

        // Берсерк при 20% HP (постійний)
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::BERSERK,
            'trigger_hp_percent' => 20,
            'config' => [
                'attack_increase_percent' => 150,
                'defense_decrease_percent' => 70,
                'permanent' => true,
            ],
            'priority' => 100,
        ]);

        // Дзеркальні відображення при 50% HP
        BossMechanic::create([
            'monster_id' => $boss->id,
            'mechanic_type' => BossMechanicType::MIRROR_IMAGE,
            'trigger_hp_percent' => 50,
            'config' => [
                'image_count' => 3,
                'image_hp_percent' => 30,
                'image_damage_percent' => 40,
            ],
            'priority' => 85,
        ]);

        // Приклад 1: Лич - 100% конвертація урону в лікування
        $lich = Monster::create([
            'name' => 'Лич Некромант',
            'type' => 'boss',
            'lvl' => 35,
            'hp' => 5000,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'armor' => 1,
            'dodge' => 1,
            'critical' => 1,
            'aggression' => 1,
            'exp' => 8000,
            'min_money' => 500000,
            'max_money' => 700000,
            'is_boss' => true,
        ]);

        $this->boss2 = $lich;

        BossMechanic::create([
            'monster_id' => $lich->id,
            'mechanic_type' => BossMechanicType::DAMAGE_TO_HEAL,
            'trigger_hp_percent' => 50,
            'config' => [
                'conversion_percent' => 100, // Весь урон перетворюється
                'duration_turns' => 3,
                'max_heal_per_hit' => null, // Без обмеження
            ],
            'priority' => 95,
        ]);

        // Приклад 2: Вампір - 75% конвертація з обмеженням
        $vampire = Monster::create([
            'name' => 'Древний вампир',
            'type' => 'boss',
            'lvl' => 40,
            'hp' => 7000,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'armor' => 1,
            'dodge' => 1,
            'critical' => 1,
            'aggression' => 1,
            'exp' => 10000,
            'min_money' => 700000,
            'max_money' => 900000,
            'is_boss' => true,
        ]);

        $this->boss3 = $vampire;

        BossMechanic::create([
            'monster_id' => $vampire->id,
            'mechanic_type' => BossMechanicType::DAMAGE_TO_HEAL,
            'trigger_hp_percent' => 60,
            'config' => [
                'conversion_percent' => 75, // 75% урону перетворюється
                'duration_turns' => 4,
                'max_heal_per_hit' => 1500, // Максимум 1500 HP за хіт
            ],
            'priority' => 90,
        ]);

        // Приклад 3: Демон регенерації - 50% конвертація, довга тривалість
        $demon = Monster::create([
            'name' => 'Демон Регенерации',
            'type' => 'boss',
            'lvl' => 45,
            'hp' => 9000,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'armor' => 1,
            'dodge' => 1,
            'critical' => 1,
            'aggression' => 1,
            'exp' => 12000,
            'min_money' => 900000,
            'max_money' => 1000000,
            'is_boss' => true,
        ]);

        $this->boss4 = $demon;

        BossMechanic::create([
            'monster_id' => $demon->id,
            'mechanic_type' => BossMechanicType::DAMAGE_TO_HEAL,
            'trigger_hp_percent' => 30,
            'config' => [
                'conversion_percent' => 50, // Половина урону перетворюється
                'duration_turns' => 6,
                'max_heal_per_hit' => 1000,
            ],
            'priority' => 85,
        ]);

        // Комбо: конвертація + регенерація
        BossMechanic::create([
            'monster_id' => $demon->id,
            'mechanic_type' => BossMechanicType::REGENERATION,
            'trigger_turn' => 5,
            'config' => [
                'heal_percent' => 3,
                'cooldown_turns' => 5,
            ],
            'priority' => 50,
        ]);

        // Приклад 4: Нежить з циклічною конвертацією
        $undead = Monster::create([
            'name' => 'Властелин Нежити',
            'lvl' => 50,
            'hp' => 10000,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'armor' => 1,
            'dodge' => 1,
            'critical' => 1,
            'aggression' => 1,
            'exp' => 15000,
            'min_money' => 1100000,
            'max_money' => 1400000,
            'is_boss' => true,
        ]);

        $this->boss5 = $undead;

        // Активується кожні 10 ходів
        BossMechanic::create([
            'monster_id' => $undead->id,
            'mechanic_type' => BossMechanicType::DAMAGE_TO_HEAL,
            'trigger_turn' => 10,
            'config' => [
                'conversion_percent' => 100,
                'duration_turns' => 2,
                'cooldown_turns' => 10,
                'max_heal_per_hit' => 2000,
            ],
            'priority' => 100,
        ]);

        $this->info('Create Boss success');
    }

    protected function createLocationHasMonsters()
    {
        $this->location1->monsters()->attach($this->monster1->id);
        $this->location2->monsters()->attach($this->monster1->id);
        $this->location2->monsters()->attach($this->monster2->id);
        $this->location3->monsters()->attach($this->boss->id);
        $this->location3->monsters()->attach($this->boss2->id);
        $this->location3->monsters()->attach($this->boss3->id);
        $this->location3->monsters()->attach($this->boss4->id);
        $this->location3->monsters()->attach($this->boss5->id);

        $this->info('Create LocationHasMonsters success');
    }

    protected function createSkills()
    {
        $skill = new Skill;
        $skill->name = 'Кулачный бой';
        $skill->type = 'combat';
        $skill->save();

        $skill1 = new Skill;
        $skill1->name = 'Стегающее оружие';
        $skill1->type = 'combat';
        $skill1->save();

        $this->skill = $skill1;

        $skill2 = new Skill;
        $skill2->name = 'Рубящее оружие';
        $skill2->type = 'combat';
        $skill2->save();

        $this->skill2 = $skill2;
    }

    protected function createItems()
    {
        $sItem = new ShareItem;
        $sItem->type = 'resource';
        $sItem->price = 10;
        $sItem->name = 'Коготь медведя';
        $sItem->description = 'Коготь медведя';
        $sItem->image = 'https://skazanie.com/img-item/e2cf53ab693662.jpg';
        $sItem->save();

        $sItem->monsters()->attach($this->monster1->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);
        $sItem->monsters()->attach($this->monster2->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);

        $item = new Item;
        $item->share_item_id = $sItem->id;
        $item->save();

        $this->location1->itemsOnLocation()->attach($item->id);

        $sItem2 = new ShareItem;
        $sItem2->type = 'weapon';
        $sItem2->skill_id = $this->skill2->id;
        $sItem2->skill_lvl = 0;
        $sItem2->price = 10;
        $sItem2->name = 'Кинжал «Ночной бури»';
        $sItem2->description = 'Кинжал «Ночной бури»';
        $sItem2->slot = ShareItemSlot::HAND;
        $sItem2->image = '/img/resource/sword_1.jpg';
        $sItem2->save();

        $sItem2->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 3, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem2->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem2->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $item2 = new Item;
        $item2->share_item_id = $sItem2->id;
        $item2->save();

        $this->location2->itemsOnLocation()->attach($item2->id);

        $sItem3 = new ShareItem;
        $sItem3->type = ShareItemType::POTION;
        $sItem3->price = 150;
        $sItem3->name = 'Сухой паек';
        $sItem3->description = 'Сухой паек';
        $sItem3->count_use = 10;
        $sItem3->is_heal = 1;
        $sItem3->image = 'https://skazanie.com/img-item/53e82794f638.jpg';
        $sItem3->save();

        $item3 = new Item;
        $item3->share_item_id = $sItem3->id;
        $item3->count_use = $sItem3->count_use;
        $item3->save();

        $this->location2->itemsOnLocation()->attach($item3->id);

        $sItem4 = new ShareItem;
        $sItem4->type = 'key';
        $sItem4->is_sell = 0;
        $sItem4->price = 0;
        $sItem4->name = 'Изумрудный ключ';
        $sItem4->description = 'Это большой ключ с изумрудным украшением.';
        $sItem4->image = '/img/resource/key.gif';
        $sItem4->is_weight = false;
        $sItem4->save();

        $item4 = new Item;
        $item4->share_item_id = $sItem4->id;
        $item4->count_use = $sItem4->count_use;
        $item4->save();

        $this->location1->itemsOnLocation()->attach($item4->id);

        $sItem5 = new ShareItem;
        $sItem5->type = 'weapon';
        $sItem5->skill_id = $this->skill2->id;
        $sItem5->skill_lvl = 5;
        $sItem5->price = 300;
        $sItem5->name = 'Вакидзаси';
        $sItem5->description = 'Вакидзаси';
        $sItem5->slot = ShareItemSlot::HAND;
        $sItem5->image = '/img/resource/sword_3.gif';
        $sItem5->save();

        $sItem5->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 2, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 4, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem5->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem5->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem6 = new ShareItem;
        $sItem6->type = 'weapon';
        $sItem6->skill_id = $this->skill2->id;
        $sItem6->skill_lvl = 10;
        $sItem6->price = 1000;
        $sItem6->break_crystal = 1;
        $sItem6->name = 'Копеш';
        $sItem6->description = 'Копеш';
        $sItem6->slot = ShareItemSlot::HAND;
        $sItem6->image = 'https://skazanie.com/img-item/032cd678c770.jpg';
        $sItem6->save();

        $sItem6->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 5, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 8, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $item3 = new Item;
        $item3->share_item_id = $sItem6->id;
        $item3->count_use = $sItem6->count_use;
        $item3->save();

        $this->location1->itemsOnLocation()->attach($item3->id);
        $sItem6->monsters()->attach($this->monster1->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);
        $sItem6->monsters()->attach($this->monster2->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);

        $sItem7 = new ShareItem;
        $sItem7->type = 'shield';
        $sItem7->price = 100;
        $sItem7->name = 'Щит «Заступник небес»';
        $sItem7->description = 'Щит «Заступник небес»';
        $sItem7->slot = ShareItemSlot::HAND;
        $sItem7->image = '/img/resource/sheild_1.jpg';
        $sItem7->save();

        $sItem7->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 14, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem7->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem7->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem8 = new ShareItem;
        $sItem8->type = 'armor';
        $sItem8->price = 2000;
        $sItem8->break_crystal = 2;
        $sItem8->name = 'Кольчуга «Ночной бури»';
        $sItem8->description = 'Кольчуга «Ночной бури»';
        $sItem8->slot = ShareItemSlot::CHAIN_ARMOR;
        $sItem8->image = '/img/resource/chain_armor_1.jpg';
        $sItem8->save();

        $sItem8->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 8, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem8->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem8->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem9 = new ShareItem;
        $sItem9->type = 'armor';
        $sItem9->price = 24000;
        $sItem9->break_crystal = 24;
        $sItem9->name = 'Нагрудник «Ночной бури»';
        $sItem9->description = 'Нагрудник «Ночной бури»';
        $sItem9->slot = ShareItemSlot::ARMOR;
        $sItem9->image = '/img/resource/armor_1.jpg';
        $sItem9->save();

        $sItem9->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 30, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem9->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem9->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $this->info('Create Items success');

        $sItem9 = new ShareItem;
        $sItem9->type = 'armor';
        $sItem9->price = 100;
        $sItem9->name = 'Шлем «Ночной бури»';
        $sItem9->description = 'Шлем «Ночной бури»';
        $sItem9->slot = ShareItemSlot::HELMET;
        $sItem9->image = '/img/resource/helm_1.jpg';
        $sItem9->save();

        $sItem9->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem9->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem9->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem10 = new ShareItem;
        $sItem10->type = 'armor';
        $sItem10->price = 90;
        $sItem10->name = 'Наручи «Ночной бури»';
        $sItem10->description = 'Наручи «Ночной бури»';
        $sItem10->slot = ShareItemSlot::GLOVES;
        $sItem10->image = '/img/resource/gloves_1.jpg';
        $sItem10->save();

        $sItem10->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 2, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem10->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem10->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem11 = new ShareItem;
        $sItem11->type = 'armor';
        $sItem11->price = 110;
        $sItem11->name = 'Сапоги «Ночной бури»';
        $sItem11->description = 'Сапоги «Ночной бури»';
        $sItem11->slot = ShareItemSlot::SHOES;
        $sItem11->image = '/img/resource/shoes.jpg';
        $sItem11->save();

        $sItem11->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem11->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem11->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem11 = new ShareItem;
        $sItem11->type = 'armor';
        $sItem11->price = 105;
        $sItem11->name = 'Легкий плащ';
        $sItem11->description = 'Легкий плащ';
        $sItem11->slot = ShareItemSlot::CLOAK;
        $sItem11->image = 'https://skazanie.com/img-item/13ed6f939925.jpg';
        $sItem11->save();

        $sItem11->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem11->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem11->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sIte12 = new ShareItem;
        $sIte12->type = ShareItemType::RESOURCE;
        $sIte12->price = 10;
        $sIte12->name = 'Слиток';
        $sIte12->description = 'Слиток';
        $sIte12->image = 'https://skazanie.com/img-item/b4b4a5eabf3578.jpg';
        $sIte12->save();

        $sIte12->monsters()->attach($this->monster1->id, ['drop_chance' => 30, 'min_count' => 1, 'max_count' => 5]);
        $sIte12->monsters()->attach($this->monster2->id, ['drop_chance' => 30, 'min_count' => 1, 'max_count' => 5]);

        $item12 = new Item;
        $item12->share_item_id = $sIte12->id;
        $item12->save();

        $this->location1->itemsOnLocation()->attach($item12->id, ['count' => 10]);

        $sIte13 = new ShareItem;
        $sIte13->type = ShareItemType::SCROLL;
        $sIte13->name = 'Сертификат «Новое имя»';
        $sIte13->description = 'Документ, подтверждающий ваше право воспользоваться услугой по смене игрового ника. Будьте внимательны! Новое имя должно быть уникально';
        $sIte13->image = '/img/resource/sert_rename.gif';
        $sIte13->is_weight = false;
        $sIte13->save();

        $sIte14 = new ShareItem;
        $sIte14->type = ShareItemType::SCROLL;
        $sIte14->name = 'Сертификат «Смена расы»';
        $sIte14->description = 'Позволяет один раз изменить расу.';
        $sIte14->image = '/img/resource/sert_obraz.gif';
        $sIte14->is_weight = false;
        $sIte14->save();

        $sIte15 = new ShareItem;
        $sIte15->type = ShareItemType::POTION;
        $sIte15->price = 1000;
        $sIte15->name = 'Эликсир жизни';
        $sIte15->description = 'После применения, этот эликсир восстанавливает 50% жизни';
        $sIte15->image = '/img/resource/life_red.gif';
        $sIte15->save();

        $sIteEffect15 = new ShareItemEffect;
        $sIteEffect15->share_item_id = $sIte15->id;
        $sIteEffect15->effect_type = ItemEffectType::HEAL_HP;
        $sIteEffect15->value = 50;
        $sIteEffect15->value_type = ItemEffectValueType::PERCENT;
        $sIteEffect15->save();

        $item15 = new Item;
        $item15->share_item_id = $sIte15->id;
        $item15->save();

        $this->user1->backpack()->attach($item15->id, ['equipped' => 0, 'count' => 200]);

        $sIte16 = new ShareItem;
        $sIte16->type = ShareItemType::POTION;
        $sIte16->price = 1500;
        $sIte16->name = 'Эликсир маны';
        $sIte16->description = 'После применения, этот эликсир восстанавливает 55% маны';
        $sIte16->image = '/img/resource/mp_red.gif';
        $sIte16->save();

        $sIteEffect16 = new ShareItemEffect;
        $sIteEffect16->share_item_id = $sIte16->id;
        $sIteEffect16->effect_type = ItemEffectType::HEAL_MP;
        $sIteEffect16->value = 55;
        $sIteEffect16->value_type = ItemEffectValueType::PERCENT;
        $sIteEffect16->save();

        $item16 = new Item;
        $item16->share_item_id = $sIte16->id;
        $item16->save();

        $this->user1->backpack()->attach($item16->id, ['equipped' => 0, 'count' => 200]);

        $sIte17 = new ShareItem;
        $sIte17->type = ShareItemType::BELT;
        $sIte17->price = 20000;
        $sIte17->name = 'Пояс титана';
        $sIte17->slot = ShareItemSlot::BELT;
        $sIte17->description = 'Пояс добавляет три слота для эликсиров, свитков и прочих вспомогательных эффектов';
        $sIte17->image = '/img/resource/bluebelt.gif';
        $sIte17->save();

        $sIte17->stats()->createMany([
            [
                'effect_type' => ShareItemStatType::BELT_SLOT,
                'value' => 3,
                'value_type' => ItemEffectValueType::FLAT,
            ],
        ]);

        $item17 = new Item;
        $item17->share_item_id = $sIte17->id;
        $item17->save();

        $this->user1->backpack()->attach($item17->id, ['equipped' => 0, 'count' => 1]);

        $sIte18 = new ShareItem;
        $sIte18->type = ShareItemType::BAG;
        $sIte18->price = 30000;
        $sIte18->name = 'Рюкзак путешественника';
        $sIte18->slot = ShareItemSlot::BAG;
        $sIte18->description = 'Кожаная сумка позволит вам взять с собой на 11 вещей больше';
        $sIte18->image = '/img/resource/bag2.gif';
        $sIte18->save();

        $sIte18->stats()->createMany([
            [
                'effect_type' => ShareItemStatType::BAG_SLOT,
                'value' => 11,
                'value_type' => ItemEffectValueType::FLAT,
            ],
        ]);

        $item18 = new Item;
        $item18->share_item_id = $sIte18->id;
        $item18->save();

        $this->user1->backpack()->attach($item18->id, ['equipped' => 0, 'count' => 1]);

        $sItem19 = new ShareItem;
        $sItem19->type = ShareItemType::SCROLL;
        $sItem19->price = 1000;
        $sItem19->name = 'Свиток заточки';
        $sItem19->description = 'Необходим для заточки предмета';
        $sItem19->image = '/img/resource/scroll_enchant.gif';
        $sItem19->upgrade_scroll_type = UpgradeScrollType::BASE;
        $sItem19->save();

        $item19 = new Item;
        $item19->share_item_id = $sItem19->id;
        $item19->save();

        $this->user1->backpack()->attach($item19->id, ['equipped' => 0, 'count' => 100]);

        $sItem19 = new ShareItem;
        $sItem19->type = ShareItemType::SCROLL;
        $sItem19->price = 5000;
        $sItem19->name = 'Свиток стабилизации';
        $sItem19->description = 'Предотвращает понижение уровня заточки при неудаче';
        $sItem19->image = '/img/resource/scroll_stabilizer.gif';
        $sItem19->upgrade_scroll_type = UpgradeScrollType::STABILIZER;
        $sItem19->save();

        $item19 = new Item;
        $item19->share_item_id = $sItem19->id;
        $item19->save();

        $this->user1->backpack()->attach($item19->id, ['equipped' => 0, 'count' => 100]);

        $sItem20 = new ShareItem;
        $sItem20->type = ShareItemType::SCROLL;
        $sItem20->price = 10000;
        $sItem20->name = 'Свиток защиты';
        $sItem20->description = 'Предотвращает уничтожение предмета при неудаче';
        $sItem20->image = '/img/resource/scroll_defense.gif';
        $sItem20->upgrade_scroll_type = UpgradeScrollType::PROTECTION;
        $sItem20->save();

        $item20 = new Item;
        $item20->share_item_id = $sItem20->id;
        $item20->save();

        $this->user1->backpack()->attach($item20->id, ['equipped' => 0, 'count' => 100]);

        $this->info('Create Items success');
    }

    protected function createRecipeItems()
    {
        $user = User::find(1);

        $sItem1 = new ShareItem;
        $sItem1->type = 'weapon';
        $sItem1->skill_id = $this->skill->id;
        $sItem1->skill_lvl = 50;
        $sItem1->price = 7000000;
        $sItem1->break_crystal = 70000;
        $sItem1->name = 'Кнут Архангела';
        $sItem1->description = 'Кнут Архангела';
        $sItem1->slot = ShareItemSlot::HAND;
        $sItem1->image = 'https://skazanie.com/img-item/8f5d6477954920.jpg';
        $sItem1->save();

        $sItem1->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 35, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 80, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $item1 = new Item;
        $item1->share_item_id = $sItem1->id;
        $item1->save();

        $sItem2 = new ShareItem;
        $sItem2->type = 'recipe';
        $sItem2->price = 1000;
        $sItem2->name = 'Рецепт "Кнут Архангела"';
        $sItem2->description = 'Рецепт "Кнут Архангела"';
        $sItem2->image = '/img/resource/scroll_weapon.gif';
        $sItem2->save();

        $item2 = new Item;
        $item2->share_item_id = $sItem2->id;
        $item2->save();

        $shareRecipe = new ShareRecipe;
        $shareRecipe->share_item_id = $sItem2->id;
        $shareRecipe->kraft_item_id = $sItem1->id;
        $shareRecipe->percent = 95;
        $shareRecipe->save();

        $sItem3 = new ShareItem;
        $sItem3->type = 'resource';
        $sItem3->price = 1000;
        $sItem3->name = 'Кристалл';
        $sItem3->description = 'Кристалл';
        $sItem3->image = '/img/resource/crystal_gold.gif';
        $sItem3->save();

        $item3 = new Item;
        $item3->share_item_id = $sItem3->id;
        $item3->save();

        $details1 = ShareItem::where('name', 'Коготь медведя')->first();
        $details2 = ShareItem::where('name', 'Слиток')->first();
        $shareRecipe->items()->attach($details1->id, ['count' => 2]);
        $shareRecipe->items()->attach($details2->id, ['count' => 1]);
        $shareRecipe->items()->attach($sItem3->id, ['count' => 100]);

        $user->backpack()->attach($item2->id, ['equipped' => 0]);
        $user->backpack()->attach($details1->id, ['equipped' => 0, 'count' => 2]);
        $user->backpack()->attach($item3->id, ['equipped' => 0, 'count' => 200]);
    }

    protected function createBoxItems()
    {
        $sItem1 = new ShareItem;
        $sItem1->type = 'chest';
        $sItem1->price = 0;
        $sItem1->break_crystal = 0;
        $sItem1->name = 'Сундук';
        $sItem1->description = 'Сундук';
        $sItem1->image = '/img/resource/chest.jpg';
        $sItem1->save();

        $details1 = ShareItem::find(3);
        $details2 = ShareItem::find(11);
        $details3 = ShareItem::find(14);

        $sItem1->itemHasItems()->attach($details1->id, ['min_count' => 1, 'max_count' => 1, 'drop_chance' => 70]);
        $sItem1->itemHasItems()->attach($details2->id, ['min_count' => 1, 'max_count' => 1, 'drop_chance' => 70]);
        $sItem1->itemHasItems()->attach($details3->id, ['min_count' => 1, 'max_count' => 3, 'drop_chance' => 70]);

        $item = new Item;
        $item->share_item_id = $sItem1->id;
        $item->save();

        $this->location1->itemsOnLocation()->attach($item->id);
    }

    public function createShareActions()
    {
        $action1 = new ShareAction;
        $action1->alias = 'heal';
        $action1->name = 'Востановить жизни';
        $action1->save();

        $action2 = new ShareAction;
        $action2->alias = 'buy';
        $action2->name = 'Купить';
        $action2->save();

        $action3 = new ShareAction;
        $action3->alias = 'sell';
        $action3->name = 'Продать';
        $action3->save();

        $action4 = new ShareAction;
        $action4->alias = 'put_item';
        $action4->name = 'Оставить на хранение';
        $action4->save();

        $action5 = new ShareAction;
        $action5->alias = 'take_item';
        $action5->name = 'Забрать вещи';
        $action5->save();

        $action6 = new ShareAction;
        $action6->alias = 'kraft_item';
        $action6->name = 'Крафт предметов';
        $action6->save();

        $action7 = new ShareAction;
        $action7->alias = 'auction_buy';
        $action7->name = 'Купить товар';
        $action7->save();

        $action8 = new ShareAction;
        $action8->alias = 'auction_sell';
        $action8->name = 'Новый лот';
        $action8->save();

        $action9 = new ShareAction;
        $action9->alias = 'auction_my_lot';
        $action9->name = 'Мои лоты';
        $action9->save();
    }

    public function createShareShopCategory()
    {
        $category1 = new ShareStructureCategory;
        $category1->name = 'Оружие';
        $category1->save();

        $this->shopCategory1 = $category1;

        $category2 = new ShareStructureCategory;
        $category2->name = 'Артефакты';
        $category2->save();

        $this->shopCategory2 = $category2;

        $category3 = new ShareStructureCategory;
        $category3->name = 'Услуги';
        $category3->save();

        $this->shopCategory3 = $category3;
    }

    public function createStructures()
    {
        $locationArmorShop = Location::find(18);
        $shop1 = new Structure;
        $shop1->type = Structure::TYPE_SHOP;
        $shop1->name = 'Магазин брони';
        $shop1->location_id = $locationArmorShop->id;
        $shop1->save();

        $shop1->shopItems()->create([
            'share_item_id' => 7,
            'price' => 100,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 8,
            'price' => 2000,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 9,
            'price' => 24000,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 10,
            'price' => 100,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 11,
            'price' => 90,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 12,
            'price' => 110,
        ]);
        $shop1->shopItems()->create([
            'share_item_id' => 13,
            'price' => 105,
        ]);

        $shop1->actions()->attach(2);
        $shop1->actions()->attach(3);

        $locationWeaponShop = Location::find(16);
        $shop2 = new Structure;
        $shop2->type = Structure::TYPE_SHOP;
        $shop2->name = 'Магазин оружия';
        $shop2->location_id = $locationWeaponShop->id;
        $shop2->save();

        $shop2->shopItems()->create([
            'share_item_id' => 2,
            'price' => 10,
        ]);
        $shop2->shopItems()->create([
            'share_item_id' => 5,
            'price' => 300,
        ]);
        $shop2->shopItems()->create([
            'share_item_id' => 6,
            'price' => 1000,
        ]);

        $shop2->actions()->attach(2);
        $shop2->actions()->attach(3);

        $locationHeal = Location::find(6);
        $shop3 = new Structure;
        $shop3->type = Structure::TYPE_HEAL;
        $shop3->name = 'Целительный фонтан';
        $shop3->location_id = $locationHeal->id;
        $shop3->save();

        $shop3->actions()->attach(1);

        $locationHeal = Location::find(47);
        $shop4 = new Structure;
        $shop4->type = Structure::TYPE_WAREHOUSE;
        $shop4->name = 'Хранилище';
        $shop4->location_id = $locationHeal->id;
        $shop4->save();

        $shop4->actions()->attach(4);
        $shop4->actions()->attach(5);

        $clanWarehouse = new Structure;
        $clanWarehouse->type = Structure::TYPE_CLAN_WAREHOUSE;
        $clanWarehouse->name = 'Клановое хранилище';
        $clanWarehouse->location_id = $locationHeal->id;
        $clanWarehouse->save();

        $clanWarehouse->actions()->attach(4);
        $clanWarehouse->actions()->attach(5);

        $locationBank = Location::find(46);
        $bank = new Structure;
        $bank->type = Structure::TYPE_BANK;
        $bank->name = 'Банк';
        $bank->location_id = $locationBank->id;
        $bank->save();

        $locationBank = Location::find(46);
        $bank = new Structure;
        $bank->type = Structure::TYPE_CLAN_BANK;
        $bank->name = 'Клановая казна';
        $bank->location_id = $locationBank->id;
        $bank->save();

        $locationHeal = Location::find(27);
        $shop5 = new Structure;
        $shop5->type = Structure::TYPE_BLACKSMITH;
        $shop5->name = 'Кузня';
        $shop5->location_id = $locationHeal->id;
        $shop5->save();

        $shop5->actions()->attach(6);

        $locationHeal = Location::find(39);
        $auction = new Structure;
        $auction->type = Structure::TYPE_AUCTION;
        $auction->name = 'Комисионный магазин';
        $auction->location_id = $locationHeal->id;
        $auction->save();

        $auction->actions()->attach(7);
        $auction->actions()->attach(8);
        $auction->actions()->attach(9);

        $item = Item::find(1);
        $auctionObj = new Auction;
        $auctionObj->user_id = $this->user1->id;
        $auctionObj->structure_id = $auction->id;
        $auctionObj->item_id = $item->id;
        $auctionObj->count = 1;
        $auctionObj->price = 200;
        $auctionObj->is_anonymous = 0;
        $auctionObj->save();

        $premium = new Structure;
        $premium->type = Structure::TYPE_SHOP;
        $premium->name = 'Премиум';
        $premium->save();

        $premium->categories()->attach($this->shopCategory1->id);
        $premium->categories()->attach($this->shopCategory2->id);
        $premium->categories()->attach($this->shopCategory3->id);

        $premium->shopItems()->createMany([
            [
                'share_item_id' => 15,
                'share_structure_category_id' => $this->shopCategory3->id,
                'diamond' => 100,
                'sort_order' => 1,
            ],
            [
                'share_item_id' => 16,
                'share_structure_category_id' => $this->shopCategory3->id,
                'diamond' => 100,
                'sort_order' => 0,
            ],
        ]);
    }

    public function createNpcAndQuest()
    {
        $locationNpc = Location::find(3);
        $npc = new Npc;
        $npc->name = 'Глава города';
        $npc->description = 'Старейшина, угрюмый лысый старик, ворчун и зануда, но большой патриот своего народа и мира. В прошлом хороший полководец, сейчас он делится опытом с воинами, обучает их всему, что знает сам.';
        $npc->location_id = $locationNpc->id;
        $npc->image = '/img/npc/stareyshina.jpg';
        $npc->save();

        $quest1 = new Quest;
        $quest1->title = 'Познание мира';
        $quest1->description = 'Уфф... Давненько до такого не доходило! Но вы, воин, вовремя подоспели и
                                                задали им! Простите, что втянул вас в этот бой. Многие воины погибли от
                                                их зубов, а чудища всё прибывают… Страшусь даже подумать, что будет
                                                дальше. <br> Постойте, я не помню вас среди жителей города, воин. Вы
                                                здесь недавно? <br> В таком случае раскрываю свои объятия и говорю:
                                                добро пожаловать, друг! Я здешний наставник, управляю этим местом. <br>
                                                Если хочешь внести свою лепту в общее дело, покажи свою силу в битве с
                                                ужасными <span><b class="blue">Неферто</b> </span>, добыв
                                                при этом – их Соцветие!
                                                А теперь ступай и постарайтесь не обмануть мои ожидания!';
        $quest1->type = QuestType::MAIN;
        $quest1->start_npc_id = $npc->id;
        $quest1->complete_npc_id = $npc->id;
        $quest1->save();

        $quest1->rewards()->saveMany([
            new QuestReward(['type' => 'money', 'amount' => 10000]),
            new QuestReward(['type' => 'experience', 'amount' => 1000]),
        ]);

        $questObjective = new QuestObjective;
        $questObjective->quest_id = $quest1->id;
        $questObjective->type = 'kill';
        $questObjective->target_type = 'monster';
        $questObjective->target_id = 1;
        $questObjective->required_amount = 5;
        $questObjective->description = 'Отправляйтесь на поиск Неферто [1] и добудьте Соцветие (10 шт).';
        $questObjective->save();

        $shop1 = new Structure;
        $shop1->type = Structure::TYPE_SHOP;
        $shop1->name = 'Редкий магазин';
        $shop1->npc_id = $npc->id;
        $shop1->save();

        $shop1->actions()->attach(2);
        $shop1->actions()->attach(3);

        $exchangeStructure = new Structure;
        $exchangeStructure->type = Structure::TYPE_EXCHANGE;
        $exchangeStructure->name = 'Обмен у кузнеца';
        $exchangeStructure->npc_id = $npc->id;
        $exchangeStructure->save();

        $sItem1 = new ShareItem;
        $sItem1->type = ShareItemType::RESOURCE;
        $sItem1->price = 10;
        $sItem1->name = 'Монета древности';
        $sItem1->description = 'Монета древности';
        $sItem1->image = '/img/resource/ancient_coin.gif';
        $sItem1->is_weight = false;
        $sItem1->save();

        $sItem2 = new ShareItem;
        $sItem2->type = ShareItemType::RESOURCE;
        $sItem2->price = 3;
        $sItem2->name = 'Синий Камень Печати';
        $sItem2->description = 'Синий Камень Печати';
        $sItem2->image = '/img/resource/water_rune.png';
        $sItem2->save();

        $sItem3 = new ShareItem;
        $sItem3->type = ShareItemType::RESOURCE;
        $sItem3->price = 5;
        $sItem3->name = 'Зеленый Камень Печати';
        $sItem3->description = 'Зеленый Камень Печати';
        $sItem3->image = '/img/resource/wind_rune.png';
        $sItem3->save();

        $sItem4 = new ShareItem;
        $sItem4->type = ShareItemType::RESOURCE;
        $sItem4->price = 10;
        $sItem4->name = 'Красный Камень Печати';
        $sItem4->description = 'Красный Камень Печати';
        $sItem4->image = '/img/resource/fire_rune.png';
        $sItem4->save();

        $exchange = new Exchange;
        $exchange->structure_id = $exchangeStructure->id;
        $exchange->from_share_item_id = $sItem2->id;
        $exchange->to_share_item_id = $sItem1->id;
        $exchange->from_amount = 1;
        $exchange->to_amount = 3;
        $exchange->sort_order = 1;
        $exchange->save();

        $exchange = new Exchange;
        $exchange->structure_id = $exchangeStructure->id;
        $exchange->from_share_item_id = $sItem3->id;
        $exchange->to_share_item_id = $sItem1->id;
        $exchange->from_amount = 1;
        $exchange->to_amount = 5;
        $exchange->sort_order = 2;
        $exchange->save();

        $exchange = new Exchange;
        $exchange->structure_id = $exchangeStructure->id;
        $exchange->from_share_item_id = $sItem4->id;
        $exchange->to_share_item_id = $sItem1->id;
        $exchange->from_amount = 1;
        $exchange->to_amount = 10;
        $exchange->sort_order = 3;
        $exchange->save();

        $locationNpc = Location::find(4);
        $npc = new Npc;
        $npc->name = 'Мудрый Финко';
        $npc->description = 'Наемник братства «Крадущиеся в ночи». Один из старожил некогда могущественной организации, он высоко ценит и чтит законы братства, считая, что наемники не должны вмешиваться во внешние конфликты и быть их участниками.';
        $npc->location_id = $locationNpc->id;
        $npc->image = '/img/npc/naemnik-noch.jpg';
        $npc->save();

        $npc2 = Npc::firstOrCreate(
            ['name' => 'Воевода Гидвер'],
            ['location_id' => 4, 'description' => 'Дока военного искусства, тактик и стратег, он посвятил свою жизнь развитию военного дела и обучению молодых солдат его премудростям.', 'image' => '/img/npc/voevoda.jpg']
        );
    }
}
