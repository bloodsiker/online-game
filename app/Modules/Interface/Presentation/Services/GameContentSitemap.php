<?php

declare(strict_types=1);

namespace App\Modules\Interface\Presentation\Services;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Carbon\CarbonInterface;

/** Публичные страницы игрового мира: предметы, монстры, NPC, кланы и игроки. */
final class GameContentSitemap
{
    /** Профили игроков ниже этого уровня — «тонкие» страницы, в sitemap их не отдаём. */
    public const MIN_PLAYER_LEVEL = 10;

    /** @return list<array{url: string, lastmod: ?string}> */
    public function entries(): array
    {
        return [
            ...$this->items(),
            ...$this->monsters(),
            ...$this->npcs(),
            ...$this->clans(),
            ...$this->players(),
        ];
    }

    /** @return list<array{url: string, lastmod: ?string}> */
    private function items(): array
    {
        $entries = [];
        foreach (ShareItem::query()->orderBy('id')->get(['id', 'updated_at']) as $item) {
            $entries[] = $this->entry(route('items.info.share', ['id' => $item->id]), $item->updated_at);
        }

        return $entries;
    }

    /** @return list<array{url: string, lastmod: ?string}> */
    private function monsters(): array
    {
        $entries = [];
        foreach (Monster::query()->where('is_training_dummy', false)->orderBy('id')->get(['id', 'updated_at']) as $monster) {
            $entries[] = $this->entry(route('info.monster.catalog', ['id' => $monster->id]), $monster->updated_at);
        }

        return $entries;
    }

    /** @return list<array{url: string, lastmod: ?string}> */
    private function npcs(): array
    {
        $entries = [];
        foreach (Npc::query()->where('is_active', true)->whereNotNull('uuid')->orderBy('id')->get(['id', 'uuid', 'updated_at']) as $npc) {
            $entries[] = $this->entry(route('info.npc', ['uuid' => $npc->uuid]), $npc->updated_at);
        }

        return $entries;
    }

    /** @return list<array{url: string, lastmod: ?string}> */
    private function clans(): array
    {
        $entries = [];
        foreach (Clan::query()->orderBy('id')->get(['id', 'updated_at']) as $clan) {
            $entries[] = $this->entry(route('clan.public', ['clan' => $clan->id]), $clan->updated_at);
        }

        return $entries;
    }

    /** @return list<array{url: string, lastmod: ?string}> */
    private function players(): array
    {
        $entries = [];
        $players = Player::query()
            ->where('lvl', '>=', self::MIN_PLAYER_LEVEL)
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->orderBy('user_id')
            ->get(['user_id', 'updated_at']);
        foreach ($players as $player) {
            $entries[] = $this->entry(route('info.user', ['id' => $player->user_id]), $player->updated_at);
        }

        return $entries;
    }

    /** @return array{url: string, lastmod: ?string} */
    private function entry(string $url, ?CarbonInterface $updatedAt): array
    {
        return ['url' => $url, 'lastmod' => $updatedAt?->toAtomString()];
    }
}
