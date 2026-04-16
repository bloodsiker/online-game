<?php

declare(strict_types=1);

namespace App\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Enums\DungeonCooldownType;
use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonSession;
use App\Models\Location\Location;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Party\Party;
use App\Models\User;
use App\Repositories\DungeonCooldownRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DungeonService
{
    public function __construct(
        private readonly DungeonCooldownRepository $cooldownRepository,
        private readonly BackpackService           $backpackService,
    ) {}

    public function enterSolo(int $dungeonId): DungeonSession
    {
        $dungeon = Dungeon::findOrFail($dungeonId);
        $user    = Auth::user();

        $this->validateEntry($dungeon, $user);
        $this->consumeEntryKey($dungeon, $user);
        $this->applyPersonalCooldown($dungeon, $user->id);

        return DB::transaction(function () use ($dungeon, $user) {
            $this->teleportUser($user, $dungeon->first_location_id);
            $session = $this->createSession($dungeon, $user->id);
            $this->spawnMonstersForSession($dungeon, $session->id);
            return $session;
        });
    }

    public function enterWithParty(int $dungeonId, Party $party): DungeonSession
    {
        $dungeon = Dungeon::findOrFail($dungeonId);
        $leader  = Auth::user();

        if (! $party->isLeader($leader->id)) {
            throw new RuntimeException('Только лидер группы может войти в данж.');
        }

        $participants = $party->members->pluck('user');

        foreach ($participants as $user) {
            $this->validateEntry($dungeon, $user);
        }

        foreach ($participants as $user) {
            $this->consumeEntryKey($dungeon, $user);
        }

        if ($dungeon->hasGlobalCooldown()) {
            $this->cooldownRepository->setGlobal(
                $dungeonId,
                now()->addSeconds($dungeon->cooldown_seconds),
            );
        }

        return DB::transaction(function () use ($dungeon, $leader, $participants) {
            // Создаём сессию лидера — она владеет монстрами
            $this->teleportUser($leader, $dungeon->first_location_id);
            $this->applyPersonalCooldown($dungeon, $leader->id);
            $leaderSession = $this->createSession($dungeon, $leader->id);

            // Спавним монстров один раз для всей группы
            $this->spawnMonstersForSession($dungeon, $leaderSession->id);

            // Создаём сессии остальных участников, ссылаясь на лидера
            foreach ($participants as $user) {
                if ($user->id === $leader->id) {
                    continue;
                }
                $this->applyPersonalCooldown($dungeon, $user->id);
                $this->teleportUser($user, $dungeon->first_location_id);
                $this->createSession($dungeon, $user->id, primarySessionId: $leaderSession->id);
            }

            return $leaderSession;
        });
    }

    public function exitDungeon(User $user): void
    {
        $session = DungeonSession::where('user_id', $user->id)->first();

        if ($session === null) {
            return;
        }

        $returnLocationId = $session->dungeon->return_location_id ?? 6;

        DB::transaction(function () use ($user, $session, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
            $this->cleanupSessionMonsters($session);
            $session->delete();
        });
    }

    public function expireSessionIfNeeded(User $user): bool
    {
        $session = DungeonSession::where('user_id', $user->id)->first();

        if ($session === null || ! $session->isExpired()) {
            return false;
        }

        $returnLocationId = $session->dungeon->return_location_id ?? 6;

        DB::transaction(function () use ($user, $session, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
            $this->cleanupSessionMonsters($session);
            $session->delete();
        });

        return true;
    }

    public function getActiveSession(int $userId): ?DungeonSession
    {
        return DungeonSession::with('dungeon')->where('user_id', $userId)->first();
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    /**
     * Спавним монстров для сессии из шаблона location_has_monsters.
     * Linear: все локации сразу.
     * Survival: только арена (first_location_id), только первая волна.
     */
    private function spawnMonstersForSession(Dungeon $dungeon, int $sessionId): void
    {
        if ($dungeon->isSurvival()) {
            // Survival: спавним только арену (первую локацию) — волна 1
            $location = Location::with('monsters')->find($dungeon->first_location_id);
            if ($location) {
                $this->spawnWaveOnLocation($location, $sessionId);
            }
            return;
        }

        $locations = Location::where('dungeon_id', $dungeon->id)
            ->with('monsters')
            ->get();

        foreach ($locations as $location) {
            foreach ($location->monsters as $monster) {
                MonsterOnLocation::create([
                    'location_id'        => $location->id,
                    'dungeon_session_id' => $sessionId,
                    'monster_id'         => $monster->id,
                    'hp_now'             => $monster->hp,
                    'hp_max'             => $monster->hp,
                    'active'             => 1,
                    'aggression'         => $monster->pivot->aggression ?? null,
                ]);
            }
        }
    }

    /**
     * Проверяет, нужно ли спавнить следующую волну survival-данжа.
     * Вызывается при каждом посещении локации (вход, возврат после боя).
     */
    public function tryAdvanceSurvivalWave(DungeonSession $session, Location $location): void
    {
        $dungeon = $location->dungeon;

        if ($dungeon === null || ! $dungeon->isSurvival() || $dungeon->wave_count === null) {
            return;
        }

        if ($location->id !== $dungeon->first_location_id) {
            return;
        }

        $dungeonSessionId = $session->monsterSessionId();

        $hasActive = MonsterOnLocation::where('location_id', $location->id)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->where('active', 1)
            ->exists();

        if ($hasActive) {
            return;
        }

        if ($session->current_wave >= $dungeon->wave_count) {
            // Все волны пройдены — выдаём награды один раз
            if (! $session->isCompleted()) {
                $this->giveCompletionRewards($dungeon, $session);
            }
            return;
        }

        $session->increment('current_wave');
        $session->refresh();

        $location->load('monsters');
        $this->spawnWaveOnLocation($location, $dungeonSessionId);
    }

    /**
     * Выдаёт награды за полное прохождение данжа.
     * Вызывается один раз: когда все волны пройдены и completed_at ещё null.
     */
    private function giveCompletionRewards(Dungeon $dungeon, DungeonSession $session): void
    {
        $session->completed_at = now();
        $session->save();

        $user    = $session->user;
        $player  = $user->player;
        $rewards = $dungeon->rewards()->with('shareItem')->get();

        foreach ($rewards as $reward) {
            $rolled = mt_rand(0, 100000) / 1000;
            if ($rolled > $reward->drop_chance) {
                continue;
            }

            $amount = $reward->randomAmount();

            match ($reward->type) {
                \App\Enums\DungeonRewardType::GOLD => tap($user, function ($u) use ($amount) {
                    $u->money += $amount;
                    $u->save();
                }),
                \App\Enums\DungeonRewardType::EXPERIENCE => tap($player, function ($p) use ($amount) {
                    $p->exp += $amount;
                    $p->save();
                }),
                \App\Enums\DungeonRewardType::ITEM => $this->backpackService->addItemByShareItem($user, $reward->shareItem, $amount),
                default => null,
            };
        }
    }

    /**
     * Спавнит волну монстров для survival-данжа.
     *
     * Количество монстров = location->count_monster.
     * Каждый монстр выбирается случайно из пула location_has_monsters.
     * Это позволяет задавать разнообразие без дублей в пивот-таблице.
     */
    public function spawnWaveOnLocation(Location $location, int $sessionId): void
    {
        $pool  = $location->monsters; // пул типов монстров из location_has_monsters
        $count = max(1, $location->count_monster);

        if ($pool->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $monster = $pool->random();
            MonsterOnLocation::create([
                'location_id'        => $location->id,
                'dungeon_session_id' => $sessionId,
                'monster_id'         => $monster->id,
                'hp_now'             => $monster->hp,
                'hp_max'             => $monster->hp,
                'active'             => 1,
                'aggression'         => $monster->pivot->aggression ?? null,
            ]);
        }
    }

    /**
     * Удаляем монстров сессии при выходе.
     * Для партии: удаляем только если это «владелец» монстров (primary_session_id == null),
     * и нет других участников, которые ещё в данже.
     */
    private function cleanupSessionMonsters(DungeonSession $session): void
    {
        $monsterSessionId = $session->monsterSessionId();

        // Если это не владелец монстров — просто выходим, монстры остаются для партии
        if ($session->primary_session_id !== null) {
            return;
        }

        // Проверяем, остались ли ещё участники группы в данже
        $othersStillInside = DungeonSession::where('primary_session_id', $session->id)->exists();

        if ($othersStillInside) {
            return;
        }

        // Удаляем все монстры сессии
        MonsterOnLocation::where('dungeon_session_id', $monsterSessionId)->delete();

        // Удаляем дроп с локаций данжа только для этой сессии
        $this->cleanupDungeonLocationItems($session->dungeon_id, $monsterSessionId);
    }

    /**
     * Удаляет предметы на локациях данжа, принадлежащие конкретной сессии.
     */
    private function cleanupDungeonLocationItems(int $dungeonId, int $dungeonSessionId): void
    {
        $locationIds = Location::where('dungeon_id', $dungeonId)->pluck('id');

        if ($locationIds->isEmpty()) {
            return;
        }

        $itemIds = DB::table('item_on_locations')
            ->whereIn('location_id', $locationIds)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->pluck('item_id');

        DB::table('item_on_locations')
            ->whereIn('location_id', $locationIds)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->delete();

        if ($itemIds->isNotEmpty()) {
            DB::table('items')->whereIn('id', $itemIds)->delete();
        }
    }

    private function validateEntry(Dungeon $dungeon, User $user): void
    {
        if (! $dungeon->is_active) {
            throw new RuntimeException('Данж недоступен.');
        }

        if ($dungeon->first_location_id === null) {
            throw new RuntimeException('Данж ещё не настроен (нет первой локации).');
        }

        $player = $user->player;
        if ($player->lvl < $dungeon->min_level) {
            throw new RuntimeException('Ваш уровень слишком низкий для этого данжа.');
        }

        if (DungeonSession::where('user_id', $user->id)->exists()) {
            throw new RuntimeException('Вы уже находитесь в данже.');
        }

        if ($dungeon->entry_location_id !== null && $user->location_id !== $dungeon->entry_location_id) {
            throw new RuntimeException('Чтобы войти, вы должны быть у входа в данж.');
        }

        if ($this->cooldownRepository->isPersonalOnCooldown($dungeon->id, $user->id)) {
            throw new RuntimeException('Данж ещё на перезарядке.');
        }

        if ($dungeon->hasGlobalCooldown() && $this->cooldownRepository->isGlobalOnCooldown($dungeon->id)) {
            throw new RuntimeException('Данж временно недоступен (глобальный кулдаун).');
        }

        if ($dungeon->requiresKey()) {
            $hasKey = $this->backpackService->getItem($user, $dungeon->entryItem) !== null;
            if (! $hasKey) {
                throw new RuntimeException('Нет ключа для входа в этот данж.');
            }
        }
    }

    private function consumeEntryKey(Dungeon $dungeon, User $user): void
    {
        if ($dungeon->requiresKey()) {
            $this->backpackService->removeItemByShareItem($user, $dungeon->entryItem, 1);
        }
    }

    private function applyPersonalCooldown(Dungeon $dungeon, int $userId): void
    {
        if ($dungeon->cooldown_type === DungeonCooldownType::PERSONAL) {
            $this->cooldownRepository->setPersonal(
                $dungeon->id,
                $userId,
                now()->addSeconds($dungeon->cooldown_seconds),
            );
        }
    }

    private function createSession(Dungeon $dungeon, int $userId, ?int $primarySessionId = null): DungeonSession
    {
        $expiresAt = $dungeon->hasDungeonTimer()
            ? Carbon::now()->addSeconds($dungeon->time_limit_seconds)
            : null;

        return DungeonSession::create([
            'dungeon_id'         => $dungeon->id,
            'user_id'            => $userId,
            'primary_session_id' => $primarySessionId,
            'expires_at'         => $expiresAt,
        ]);
    }

    private function teleportUser(User $user, int $locationId): void
    {
        $user->location_id = $locationId;
        $user->save();
    }
}
