<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\Mappers;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Rating\Application\DTOs\RatingEntryDTO;
use App\Modules\Rating\Application\DTOs\RatingMenuItemDTO;
use App\Modules\Rating\Application\DTOs\RatingPageDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class RatingPageViewMapper
{
    /**
     * @param  array<string, array{title:string,name:string,column:string}>  $menu
     */
    public function map(User $user, array $menu, string $type, bool $isSkillRating, LengthAwarePaginator $players): RatingPageDTO
    {
        $currentPage = $players->currentPage();
        $lastPage = $players->lastPage();
        $pageFrom = max(1, $currentPage - 5);
        $pageTo = min($lastPage, $currentPage + 5);
        $urls = [];

        for ($page = 1; $page <= $lastPage; $page++) {
            $urls[$page] = $players->url($page);
        }

        return new RatingPageDTO(
            title: $menu[$type]['title'],
            columnName: $menu[$type]['name'],
            type: $type,
            currentUserName: mb_strtolower($user->name),
            menu: collect($menu)->map(
                static fn (array $menuItem, string $key): RatingMenuItemDTO => new RatingMenuItemDTO(
                    key: $key,
                    title: $menuItem['title'],
                    name: $menuItem['name'],
                    isActive: $key === $type,
                    isSkill: str_starts_with($key, 'skill_'),
                )
            )->values()->all(),
            entries: collect($players->items())->values()->map(
                static function (Player|PlayerSkill $item, int $index) use ($isSkillRating, $players, $menu, $type): RatingEntryDTO {
                    $rowPlayer = $isSkillRating ? $item->player : $item;
                    $rowValue = $isSkillRating
                        ? $item->lvl
                        : (int) data_get($item, $menu[$type]['column']);
                    $clan = $rowPlayer->user->clanMembership?->clan;

                    return new RatingEntryDTO(
                        position: $players->firstItem() + $index,
                        userId: $rowPlayer->user->id,
                        userName: $rowPlayer->user->name,
                        level: (int) $rowPlayer->lvl,
                        hasClan: $clan !== null,
                        clanId: $clan?->id,
                        clanName: $clan?->name,
                        clanIconUrl: $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
                        value: $rowValue,
                    );
                }
            )->all(),
            pagination: [
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'pageFrom' => $pageFrom,
                'pageTo' => $pageTo,
                'urls' => $urls,
                'onFirstPage' => $players->onFirstPage(),
                'hasMorePages' => $players->hasMorePages(),
                'previousPageUrl' => $players->previousPageUrl(),
                'nextPageUrl' => $players->nextPageUrl(),
                'firstItem' => $players->firstItem() ?? 1,
            ],
        );
    }
}
