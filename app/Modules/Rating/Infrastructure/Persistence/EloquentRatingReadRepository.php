<?php

declare(strict_types=1);

namespace App\Modules\Rating\Infrastructure\Persistence;

use App\Models\Skill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Rating\Domain\Contracts\RatingReadRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentRatingReadRepository implements RatingReadRepository
{
    public function getSkills(): Collection
    {
        return Skill::query()->orderBy('id')->get();
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
}
