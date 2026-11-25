<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class MonsterLootService
{
    /** @param  (Closure(int): ?int)|null  $partyResolver  id игрока → id его активной группы (по умолчанию из БД) */
    public function __construct(private readonly ?Closure $partyResolver = null) {}

    public function damage(MonsterOnLocation $monster, int $damage, ?int $userId, ?int $battleId = null, int $minimumHp = 0): void
    {
        // Тренировочное чучело не умирает: всегда остаётся минимум 1 HP, а вклад в лут не пишем.
        $isDummy = $monster->monster?->isTrainingDummy() === true;
        if ($isDummy) {
            $minimumHp = max($minimumHp, 1);
        }

        $actualDamage = min(max(0, $monster->hp_now - $minimumHp), max(0, $damage));
        $monster->hp_now -= $actualDamage;
        if ($isDummy || $actualDamage === 0 || $userId === null || $monster->isClanBoss()) {
            return;
        }

        $battleId ??= BattleDetail::query()
            ->where('location_monster_id', $monster->id)
            ->whereHas('battle', fn ($query) => $query->where('status', BattleStatus::ACTIVE))
            ->latest('id')->value('battle_id');
        if ($battleId === null) {
            return;
        }

        DB::table('battle_monster_damage')->insert([
            'battle_id' => $battleId,
            'location_monster_id' => $monster->id,
            'user_id' => $userId,
            'damage' => $actualDamage,
        ]);
    }

    public function recipient(int $battleId, int $monsterId, User $fallback): User
    {
        // При равном уроне побеждает раньше достигший итоговой суммы.
        $userId = DB::table('battle_monster_damage')
            ->where('battle_id', $battleId)
            ->where('location_monster_id', $monsterId)
            ->select('user_id')->groupBy('user_id')
            ->orderByRaw('SUM(damage) DESC')->orderByRaw('MAX(id) ASC')
            ->value('user_id');

        return $userId === null || (int) $userId === (int) $fallback->id
            ? $fallback
            : (User::without('player')->find($userId) ?? $fallback);
    }

    /**
     * Урон по игрокам за бой по конкретному монстру.
     *
     * @return array<int, array{damage: int, last_id: int}> user_id => суммарный урон и id последней записи
     */
    public function damageByUser(int $battleId, int $monsterId): array
    {
        $rows = DB::table('battle_monster_damage')
            ->where('battle_id', $battleId)
            ->where('location_monster_id', $monsterId)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, SUM(damage) as damage, MAX(id) as last_id')
            ->groupBy('user_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->user_id] = ['damage' => (int) $row->damage, 'last_id' => (int) $row->last_id];
        }

        return $result;
    }

    /**
     * Делит $total монет между всеми, кто бил монстра, пропорционально урону.
     * Каждый получает целое число монет, остаток от округления уходит тому, кто нанёс больше урона.
     * Нет данных об уроне (например, клановый босс) — всё получает $fallback.
     *
     * @return list<array{user_id: int, amount: int, percent: int}>
     */
    public function moneyShares(int $battleId, int $monsterId, int $total, int $fallbackUserId): array
    {
        $damage = $this->damageByUser($battleId, $monsterId);
        $sum = array_sum(array_column($damage, 'damage'));
        if ($total <= 0 || $sum <= 0) {
            return $total > 0 ? [['user_id' => $fallbackUserId, 'amount' => $total, 'percent' => 100]] : [];
        }

        // Больше урона — раньше; при равенстве раньше достигший итоговой суммы (меньший id последней записи).
        uasort($damage, static fn (array $a, array $b): int => [$b['damage'], $a['last_id']] <=> [$a['damage'], $b['last_id']]);

        $shares = [];
        $given = 0;
        foreach ($damage as $userId => $row) {
            $amount = intdiv($total * $row['damage'], $sum);
            $given += $amount;
            $shares[] = ['user_id' => (int) $userId, 'amount' => $amount, 'percent' => (int) round(100 * $row['damage'] / $sum)];
        }
        $shares[0]['amount'] += $total - $given;

        return array_values(array_filter($shares, static fn (array $share): bool => $share['amount'] > 0));
    }

    /**
     * Кому закреплён лут: сравниваются «пакеты» — группа (суммарный урон её участников) или одиночка.
     * Побеждает пакет с большим уроном; внутри группы предметы получает автолутом самый «бьющий» её участник,
     * а подобрать их в первую минуту может любой член группы.
     *
     * @return array{user: User, party_id: ?int}
     */
    public function lootOwner(int $battleId, int $monsterId, User $fallback): array
    {
        $damage = $this->damageByUser($battleId, $monsterId);
        if ($damage === []) {
            return ['user' => $fallback, 'party_id' => null];
        }

        $units = [];
        foreach ($damage as $userId => $row) {
            $partyId = $this->partyIdFor((int) $userId);
            $key = $partyId !== null ? 'p'.$partyId : 'u'.$userId;
            $units[$key]['party_id'] = $partyId;
            $units[$key]['damage'] = ($units[$key]['damage'] ?? 0) + $row['damage'];
            $units[$key]['last_id'] = max($units[$key]['last_id'] ?? 0, $row['last_id']);
            $units[$key]['members'][(int) $userId] = $row;
        }
        uasort($units, static fn (array $a, array $b): int => [$b['damage'], $a['last_id']] <=> [$a['damage'], $b['last_id']]);
        $winner = array_values($units)[0];

        $members = $winner['members'];
        uasort($members, static fn (array $a, array $b): int => [$b['damage'], $a['last_id']] <=> [$a['damage'], $b['last_id']]);
        $userId = (int) array_key_first($members);

        return [
            'user' => $userId === (int) $fallback->id ? $fallback : (User::without('player')->find($userId) ?? $fallback),
            'party_id' => $winner['party_id'],
        ];
    }

    private function partyIdFor(int $userId): ?int
    {
        if ($this->partyResolver !== null) {
            return ($this->partyResolver)($userId);
        }

        try {
            return app(PartyRepositoryInterface::class)->findActiveByUser($userId)?->id;
        } catch (\Throwable $exception) {
            // Сбой поиска группы не должен ломать выдачу лута: считаем игрока одиночкой.
            report($exception);

            return null;
        }
    }
}
