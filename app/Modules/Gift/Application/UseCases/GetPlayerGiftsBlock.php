<?php

declare(strict_types=1);

namespace App\Modules\Gift\Application\UseCases;

use App\Modules\Gift\Application\DTOs\PlayerGiftsBlockDTO;
use App\Modules\Gift\Domain\Contracts\PlayerGiftRepository;
use Illuminate\Support\Facades\Schema;

/** Блок «Подарки» в публичной информации игрока: последние подарки и ссылка на все. */
final class GetPlayerGiftsBlock
{
    public const LIMIT = 12;

    public function __construct(private readonly PlayerGiftRepository $gifts) {}

    public function execute(int $recipientId): ?PlayerGiftsBlockDTO
    {
        if (! Schema::hasTable('player_gifts')) {
            return null;
        }
        $total = $this->gifts->countFor($recipientId);

        return new PlayerGiftsBlockDTO(
            gifts: $total > 0 ? $this->gifts->latestFor($recipientId, self::LIMIT) : collect(),
            total: $total,
            allUrl: route('gifts.index', ['id' => $recipientId]),
        );
    }
}
