<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\MagicSkill\Application\DTOs\LearnMagicSkillResultDTO;
use App\Modules\MagicSkill\Application\Services\MagicSkillRequirementService;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Единственное место, где проверяются magic_skill_requirements — см. спеку,
 * правило 3: экипировка уже изученного заклинания требований не проверяет.
 */
class LearnMagicSkillFromBook
{
    public function __construct(
        private readonly MagicSkillRequirementService $requirementService,
    ) {}

    public function execute(User $user, int $shareItemId): LearnMagicSkillResultDTO
    {
        $player = $user->player;

        $book = MagicSkillBook::where('share_item_id', $shareItemId)->with(['magicSkill', 'shareItem'])->first();

        if ($book === null) {
            return new LearnMagicSkillResultDTO(ok: false, message: 'Это не книга заклинаний', httpCode: 422);
        }

        $skill = $book->magicSkill;
        $shareItem = $book->shareItem;

        // Строка magic_skill_books могла осиротеть: предмету сменили тип, а
        // привязка осталась (см. ItemController::syncMagicSkillBook). Учить с
        // не-книги нельзя — тип предмета авторитетнее наличия строки связи.
        if ($shareItem === null || $shareItem->type !== ShareItemType::BOOK) {
            return new LearnMagicSkillResultDTO(ok: false, message: 'Это не книга заклинаний', httpCode: 422);
        }

        try {
            return DB::transaction(function () use ($user, $player, $shareItem, $skill): LearnMagicSkillResultDTO {
                // Сериализуем все изучения конкретного игрока. Это предотвращает
                // обход unique-ограничения двумя параллельными запросами.
                $lockedPlayer = Player::query()->whereKey($player->id)->lockForUpdate()->first();
                if ($lockedPlayer === null) {
                    return new LearnMagicSkillResultDTO(ok: false, message: 'Игрок не найден', httpCode: 404);
                }

                $alreadyLearned = DB::table('player_magic_skills')
                    ->where('player_id', $lockedPlayer->id)
                    ->where('magic_skill_id', $skill->id)
                    ->exists();
                if ($alreadyLearned) {
                    return new LearnMagicSkillResultDTO(ok: false, message: 'Заклинание уже изучено', httpCode: 422);
                }

                $unmet = $this->requirementService->check($lockedPlayer, $skill);
                if ($unmet !== null) {
                    return new LearnMagicSkillResultDTO(ok: false, message: $unmet, httpCode: 422);
                }

                // Блокируем именно строку рюкзака до проверки count и списания:
                // два одновременных клика не смогут использовать одну книгу.
                $backpackItem = Backpack::query()
                    ->where('user_id', $user->id)
                    ->where('equipped', 0)
                    ->whereHas('item', fn ($query) => $query->where('share_item_id', $shareItem->id))
                    ->lockForUpdate()
                    ->first();
                if ($backpackItem === null || $backpackItem->count < 1) {
                    return new LearnMagicSkillResultDTO(ok: false, message: 'У вас нет этой книги', httpCode: 422);
                }

                // Unique(player_id, magic_skill_id) остаётся последней защитой
                // на уровне БД; при ошибке транзакция откатит и расход книги.
                DB::table('player_magic_skills')->insert([
                    'player_id' => $lockedPlayer->id,
                    'magic_skill_id' => $skill->id,
                    'is_equipped' => false,
                    'cooldown_end_at' => null,
                    'sort_order' => 0,
                ]);

                if ($backpackItem->count > 1) {
                    $backpackItem->decrement('count');
                } else {
                    $itemId = $backpackItem->item_id;
                    $backpackItem->delete();

                    if (! Backpack::where('item_id', $itemId)->exists()) {
                        Item::whereKey($itemId)->delete();
                    }
                }

                return new LearnMagicSkillResultDTO(ok: true, message: sprintf('Выучено: «%s»', $skill->name));
            });
        } catch (QueryException $e) {
            if (! $this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            return new LearnMagicSkillResultDTO(ok: false, message: 'Заклинание уже изучено', httpCode: 422);
        }
    }

    /**
     * SQLSTATE 23000 — integrity constraint violation, тот же код что у MySQL
     * (Duplicate entry) и у SQLite (UNIQUE constraint failed), которые
     * используются в dev/test и в проде соответственно (см. CLAUDE.md).
     */
    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return $e->getCode() === '23000';
    }
}
