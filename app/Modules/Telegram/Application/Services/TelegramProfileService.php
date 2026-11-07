<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Event\Application\UseCases\GetPlayerMapInfluences;
use App\Modules\Interface\Domain\Contracts\OnlinePlayersReader;
use App\Modules\Player\Application\UseCases\GetCharacter;
use App\Modules\Reputation\Application\UseCases\GetReputationListPage;
use App\Modules\User\Domain\Contracts\CharacterDetailsReader;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class TelegramProfileService
{
    public function __construct(
        private readonly CharacterDetailsReader $characters,
        private readonly GetCharacter $getCharacter,
        private readonly GetReputationListPage $getReputations,
        private readonly GetPlayerMapInfluences $getInfluences,
        private readonly OnlinePlayersReader $online,
        private readonly TelegramReadService $read,
    ) {}

    /** @return array<string, mixed> */
    public function character(int $userId): array
    {
        $summary = $this->read->character(userId: $userId);
        $user = $this->user(userId: $userId);
        $player = $user->player;
        $player->setRelation('user', $user);
        $character = $this->getCharacter->execute(player: $player);
        $sheet = $character->stats;
        $stats = [
            'Сила' => $sheet->getStrength(), 'Ловкость' => $sheet->getAgility(), 'Интуиция' => $sheet->getInt(),
            'Мудрость' => $sheet->getMud(), 'Интеллект' => $sheet->getIntelligence(), 'Выносливость' => $sheet->getEndurance(),
            'Броня' => $sheet->getArmor(), 'Уворот' => $sheet->getDodge(), 'Критический удар' => $sheet->getCritical(),
            'Урон' => $character->weaponDamage['min'].'–'.$character->weaponDamage['max'],
            'Магическая атака' => $sheet->getMagicAttack(), 'Сопротивление магии' => $sheet->getMagicResistance(),
            'Магический крит' => $sheet->getMagicCriticalChance().'%',
        ];
        $groups = [];
        foreach (['combat', 'peaceful'] as $type) {
            $skills = collect($character->skills)->filter(static fn ($skill): bool => $skill->type === $type)
                ->sortBy('name')->map(static fn ($skill): array => ['name' => $skill->name, 'level' => $skill->level,
                    'progress' => $skill->expPercent()])->values()->all();
            $groups[] = ['key' => $type, 'label' => __('telegram.profile_'.$type.'_skills'), 'skills' => $skills];
        }

        return $summary + ['avatar_url' => asset($user->profile?->avatar ?: 'img/avatar/dark_elf.jpg'),
            'race' => $character->raceName, 'stats' => collect($stats)->map(static fn ($value, string $label): array => [
                'label' => __($label), 'value' => $value,
            ])->values()->all(), 'skills' => $groups,
            'resources' => [
                ['key' => 'hp', 'label' => __('Здоровье'), 'current' => $character->hpNow, 'max' => $sheet->getHpMax()],
                ['key' => 'mp', 'label' => __('Мана'), 'current' => $character->mpNow, 'max' => $sheet->getMpMax()],
                ['key' => 'exp', 'label' => __('Опыт'), 'current' => $character->exp, 'max' => $character->expUp, 'progress' => $character->expPercent],
            ]];
    }

    /** @return list<array<string, mixed>> */
    public function reputations(int $userId): array
    {
        $user = $this->user(userId: $userId);

        return $this->getReputations->execute(player: $user->player)->playerReputations->map(function (array $row): array {
            $points = (int) $row['pr']->points;
            $start = (int) ($row['currentTier']?->min_points ?? 0);
            $next = $row['nextTier']?->min_points;

            return ['id' => $row['reputation']->id, 'name' => $row['reputation']->name,
                'icon' => $row['reputation']->icon ? asset(resolve_storage_image_url($row['reputation']->icon)) : null,
                'points' => $points, 'level_name' => $row['currentTier']?->medal_name,
                'next' => $next, 'progress' => $this->progress(points: $points, start: $start, next: $next)];
        })->values()->all();
    }

    /** @return list<array<string, mixed>> */
    public function influences(int $userId): array
    {
        $this->read->character(userId: $userId);

        return $this->getInfluences->execute(userId: $userId)->filter(static fn ($row): bool => $row->map !== null)
            ->map(function ($row): array {
                return ['id' => $row->map_id, 'name' => $row->map->name, 'points' => (int) $row->influence,
                    'icon' => $row->currentLevel?->medal?->iconUrl(), 'level_name' => $row->currentLevel?->name,
                    'next' => $row->nextLevel?->required_influence,
                    'progress' => $this->progress(points: (int) $row->influence,
                        start: (int) ($row->currentLevel?->required_influence ?? 0), next: $row->nextLevel?->required_influence)];
            })->values()->all();
    }

    public function onlineCount(int $userId): int
    {
        $this->read->character(userId: $userId);

        return $this->online->count();
    }

    /** @return array<string, mixed> */
    public function onlinePlayers(int $userId, int $page): array
    {
        $this->read->character(userId: $userId);
        $players = $this->online->page(page: $page, perPage: 25);

        return ['online_count' => $players->total(), 'data' => $players->items(), 'meta' => [
            'current_page' => $players->currentPage(), 'last_page' => $players->lastPage(), 'total' => $players->total(),
        ]];
    }

    private function user(int $userId): User
    {
        $user = $this->characters->verified(userId: $userId);
        abort_unless($user?->player !== null, 403, __('telegram.account_unavailable'));

        return $user;
    }

    private function progress(int $points, int $start, ?int $next): int
    {
        return $next === null ? ($points > 0 ? 100 : 0)
            : min(100, max(0, (int) round(($points - $start) * 100 / max(1, $next - $start))));
    }
}
