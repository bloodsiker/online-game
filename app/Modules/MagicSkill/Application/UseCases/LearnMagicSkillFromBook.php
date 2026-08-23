<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\MagicSkill\Application\DTOs\LearnMagicSkillResultDTO;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\MagicSkillRequirementService;
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
        private readonly BackpackService $backpackService,
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

        $alreadyLearned = DB::table('player_magic_skills')
            ->where('player_id', $player->id)
            ->where('magic_skill_id', $skill->id)
            ->exists();

        if ($alreadyLearned) {
            return new LearnMagicSkillResultDTO(ok: false, message: 'Заклинание уже изучено', httpCode: 422);
        }

        if (! $this->backpackService->hasItemByShareItem($user, $shareItem)) {
            return new LearnMagicSkillResultDTO(ok: false, message: 'У вас нет этой книги', httpCode: 422);
        }

        $unmet = $this->requirementService->check($player, $skill);

        if ($unmet !== null) {
            return new LearnMagicSkillResultDTO(ok: false, message: $unmet, httpCode: 422);
        }

        try {
            return DB::transaction(function () use ($user, $player, $shareItem, $skill): LearnMagicSkillResultDTO {
                // Повторная проверка владения книгой внутри транзакции (на случай гонки между
                // проверкой требований выше и этим моментом) — removeItemByShareItem() сам
                // подтверждает наличие ≥1 экземпляра и списывает ровно одну единицу: удаляет
                // строку рюкзака, если count достигает 0, иначе декрементирует count.
                $removed = $this->backpackService->removeItemByShareItem($user, $shareItem);

                if (! $removed) {
                    return new LearnMagicSkillResultDTO(ok: false, message: 'У вас нет этой книги', httpCode: 422);
                }

                // Финальный, авторитетный барьер против гонки за player_magic_skills:
                // insert() полагается на unique(player_id, magic_skill_id) и бросит
                // QueryException, если конкурентный запрос успел вставить строку между
                // проверкой alreadyLearned выше и этим моментом. Ловим исключение снаружи
                // транзакции (см. catch ниже), чтобы откат забрал с собой и списание книги —
                // проигравший гонки не должен терять книгу впустую.
                DB::table('player_magic_skills')->insert([
                    'player_id' => $player->id,
                    'magic_skill_id' => $skill->id,
                    'is_equipped' => false,
                    'cooldown_end_at' => null,
                    'sort_order' => 0,
                ]);

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
