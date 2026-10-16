<?php

declare(strict_types=1);

namespace App\Modules\Rating\Infrastructure\Persistence;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossDefeatLog;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Rating\Domain\Contracts\RatingReadRepository;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentRatingReadRepository implements RatingReadRepository
{
    public function getSkills(): Collection
    {
        return Skill::query()
            ->orderByRaw("CASE WHEN type = 'peaceful' THEN 1 ELSE 0 END")
            ->orderBy('type')
            ->orderBy('id')
            ->get();
    }

    public function paginateLevelRating(int $perPage): LengthAwarePaginator
    {
        return Player::with(['user.clanMembership.clan'])
            ->orderByDesc('lvl')
            ->orderByDesc('exp')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateVictoriesRating(int $perPage): LengthAwarePaginator
    {
        return Player::with(['user.clanMembership.clan'])
            ->orderByDesc('victory')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateDeathsRating(int $perPage): LengthAwarePaginator
    {
        return Player::with(['user.clanMembership.clan'])
            ->orderByDesc('death')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateWealthRating(int $perPage): LengthAwarePaginator
    {
        return Player::with(['user.clanMembership.clan'])
            ->join('users', 'players.user_id', '=', 'users.id')
            ->orderByDesc('users.money')
            ->select('players.*')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateReputationRating(int $perPage): LengthAwarePaginator
    {
        return Player::with(['user.clanMembership.clan'])
            ->orderByDesc('reputation_rating')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDefeatedBosses(): Collection
    {
        return Monster::query()
            ->where('is_boss', true)
            ->whereIn('id', BossDefeatLog::query()->select('monster_id')->distinct())
            ->orderBy('name->ru')
            ->get();
    }

    public function paginateBossDefeatRating(int $monsterId, int $perPage): LengthAwarePaginator
    {
        return $this->bossDefeatRatingQuery($monsterId)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getInfluenceMaps(): Collection
    {
        return Map::query()
            ->where(function (Builder $query): void {
                $query->whereHas('influenceLevels')
                    ->orWhereIn('id', DB::table('map_influences')->select('map_id'));
            })
            ->orderBy('name->ru')
            ->get();
    }

    public function paginateMapInfluenceRating(int $mapId, int $perPage): LengthAwarePaginator
    {
        return $this->mapInfluenceRatingQuery($mapId)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateSkillRating(int $skillId, int $perPage): LengthAwarePaginator
    {
        return PlayerSkill::with('player.user.clanMembership.clan')
            ->where('skill_id', $skillId)
            ->orderByDesc('lvl')
            ->orderByDesc('exp')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findLevelRatingPosition(string $nick): ?int
    {
        return $this->findPlayerPosition(
            Player::with('user')->orderByDesc('lvl')->orderByDesc('exp'),
            $nick,
        );
    }

    public function findVictoriesRatingPosition(string $nick): ?int
    {
        return $this->findPlayerPosition(
            Player::with('user')->orderByDesc('victory'),
            $nick,
        );
    }

    public function findDeathsRatingPosition(string $nick): ?int
    {
        return $this->findPlayerPosition(
            Player::with('user')->orderByDesc('death'),
            $nick,
        );
    }

    public function findWealthRatingPosition(string $nick): ?int
    {
        return $this->findPlayerPosition(
            Player::with('user')
                ->join('users', 'players.user_id', '=', 'users.id')
                ->orderByDesc('users.money')
                ->select('players.*'),
            $nick,
        );
    }

    public function findReputationRatingPosition(string $nick): ?int
    {
        return $this->findPlayerPosition(
            Player::with('user')->orderByDesc('reputation_rating')->orderByDesc('id'),
            $nick,
        );
    }

    public function findBossDefeatRatingPosition(int $monsterId, string $nick): ?int
    {
        return $this->findPlayerPosition($this->bossDefeatRatingQuery($monsterId), $nick);
    }

    public function findMapInfluenceRatingPosition(int $mapId, string $nick): ?int
    {
        return $this->findPlayerPosition($this->mapInfluenceRatingQuery($mapId), $nick);
    }

    public function findSkillRatingPosition(int $skillId, string $nick): ?int
    {
        $position = null;
        $chunk = 0;

        PlayerSkill::with('player.user')
            ->where('skill_id', $skillId)
            ->orderByDesc('lvl')
            ->orderByDesc('exp')
            ->chunk(200, function ($items) use ($nick, &$position, &$chunk) {
                foreach ($items as $item) {
                    $chunk++;
                    if (mb_strtolower($item->player->user->name ?? '') === mb_strtolower($nick)) {
                        $position = $chunk;

                        return false;
                    }
                }
            });

        return $position;
    }

    private function findPlayerPosition($query, string $nick): ?int
    {
        $position = null;
        $chunk = 0;

        $query->chunk(200, function ($players) use ($nick, &$position, &$chunk) {
            foreach ($players as $player) {
                $chunk++;
                if (mb_strtolower($player->user->name ?? '') === mb_strtolower($nick)) {
                    $position = $chunk;

                    return false;
                }
            }
        });

        return $position;
    }

    private function mapInfluenceRatingQuery(int $mapId): Builder
    {
        return Player::query()
            ->with(['user.clanMembership.clan'])
            ->join('map_influences', 'players.user_id', '=', 'map_influences.user_id')
            ->where('map_influences.map_id', $mapId)
            ->select('players.*')
            ->selectRaw('map_influences.influence AS map_influence')
            ->orderByDesc('map_influence')
            ->orderBy('players.id');
    }

    private function bossDefeatRatingQuery(int $monsterId): Builder
    {
        $defeats = BossDefeatLog::query()
            ->selectRaw('player_id, COUNT(*) AS boss_defeats')
            ->where('monster_id', $monsterId)
            ->groupBy('player_id');

        return Player::query()
            ->with(['user.clanMembership.clan'])
            ->joinSub($defeats, 'boss_defeats', 'boss_defeats.player_id', '=', 'players.id')
            ->select('players.*')
            ->selectRaw('boss_defeats.boss_defeats')
            ->orderByDesc('boss_defeats.boss_defeats')
            ->orderBy('players.id');
    }
}
