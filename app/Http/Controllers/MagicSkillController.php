<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\AttackResultDTO;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Player\Player;
use App\Services\Combat\BattleEffectService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicSkillController extends Controller
{
    public function __construct(
        private PlayerStatService   $statService,
        private BattleEffectService $effectService,
    ) {}

    public function index(Request $request)
    {
        $user   = Auth::user();
        $player = $user->player;

        $group = $request->get('group', 'magic_skill');

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(fn ($s) => $s->is_passive);

        // Список онлайн-игроков на той же локации для выбора цели (кроме себя)
        $allyTargets = Player::whereHas('user', fn ($q) => $q
                ->where('location_id', $user->location_id)
                ->where('id', '!=', $user->id)
                ->where('last_online_at', '>=', now()->subMinutes(10))
            )
            ->with('user:id,name')
            ->get(['id', 'user_id']);

        return view('magic_skill.index', compact('user', 'player', 'group', 'passiveSkills', 'activeSkills', 'allyTargets'));
    }

    public function updateSkill(Request $request): JsonResponse
    {
        $user   = Auth::user();
        $player = $user->player;

        $oldSheet    = $this->statService->resolve($player);
        $equippedIds = $request->input('skills', []);

        $player->magicSkills()->update(['is_equipped' => false]);
        if (count($equippedIds)) {
            $player->magicSkills()
                ->whereIn('magic_skill_id', $equippedIds)
                ->update(['is_equipped' => true]);
        }

        $player->refresh();
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        $message = count($equippedIds) ? 'Сохранено' : 'Сохранено. Не выбрано ни одного скилла';

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $player = Auth::user()->player;
        $ids    = $request->input('ids', []);

        foreach ($ids as $index => $skillId) {
            $player->magicSkills()
                ->wherePivot('magic_skill_id', (int) $skillId)
                ->updateExistingPivot((int) $skillId, ['sort_order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Use a buff/heal skill outside of battle.
     * target_player_id — optional, defaults to self.
     */
    public function useSkill(Request $request, MagicSkill $skill): JsonResponse
    {
        $user   = Auth::user();
        $caster = $user->player;

        // Проверяем что скилл принадлежит игроку
        $owns = $caster->magicSkills()->where('magic_skill_id', $skill->id)->exists();
        if (!$owns) {
            return response()->json(['status' => 'error', 'message' => 'Заклинание не изучено'], 403);
        }

        if (!$skill->isBuffSkill()) {
            return response()->json(['status' => 'error', 'message' => 'Это заклинание нельзя использовать вне боя'], 422);
        }

        // Проверяем кулдаун
        $pivot = $caster->magicSkills()->where('magic_skill_id', $skill->id)->first()?->pivot;
        if ($pivot?->cooldown_end_at && now()->lt($pivot->cooldown_end_at)) {
            $remaining = (int) now()->diffInSeconds($pivot->cooldown_end_at, false);
            return response()->json(['status' => 'error', 'message' => sprintf('Заклинание на перезарядке ещё %d сек.', $remaining)], 422);
        }

        if ($caster->mp_now < $skill->mana_cost) {
            return response()->json(['status' => 'error', 'message' => sprintf('Недостаточно маны. Нужно: %d MP', $skill->mana_cost)], 422);
        }

        // Определяем цель
        $targetId = $request->input('target_player_id');
        $target   = $targetId ? Player::find($targetId) : $caster;

        if (!$target) {
            return response()->json(['status' => 'error', 'message' => 'Цель не найдена'], 404);
        }

        $casterSheet = $this->statService->resolve($caster);
        $targetSheet = $target->id === $caster->id ? $casterSheet : $this->statService->resolve($target);

        // Снимаем ману с кастера
        $caster->mp_now -= $skill->mana_cost;

        $log = new AttackResultDTO();

        // Лечение
        if ($skill->base_healing > 0) {
            $heal = $skill->base_healing;
            $target->hp_now = min($targetSheet->getHpMax(), $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }

        // Эффекты из skillEffects (с проверкой шанса)
        $skill->load('skillEffects');
        $appliedBlessings = [];
        foreach ($skill->skillEffects as $effect) {
            if (random_int(1, 100) <= $effect->pivot->chance) {
                $this->effectService->applyEffectToPlayer($effect, $target, null, $log);
                if ($target->id === $caster->id) {
                    $appliedBlessings[] = [
                        'id'       => $effect->slug . '_' . time(),
                        'name'     => $effect->name,
                        'duration' => (int) $effect->duration,
                    ];
                }
            }
        }

        $caster->save();
        if ($target->id !== $caster->id) {
            $target->save();
        }

        // Сохраняем кулдаун в pivot
        $cooldownEndsAt = $skill->cooldown > 0 ? now()->addSeconds($skill->cooldown) : null;
        $caster->magicSkills()->updateExistingPivot($skill->id, ['cooldown_end_at' => $cooldownEndsAt]);

        // После применения эффектов пересчитываем кастера (баффы могли измениться)
        $freshSheet = $this->statService->resolve($caster->fresh());

        return response()->json([
            'status'         => 'success',
            'message'        => $log->getLog() ?: sprintf('Применено: «%s»', $skill->name),
            'hp'             => ['current' => $caster->hp_now, 'max' => $freshSheet->getHpMax()],
            'mp'             => ['current' => $caster->mp_now, 'max' => $freshSheet->getMpMax()],
            'cooldown_until' => $cooldownEndsAt?->getTimestamp(),
            'blessings'      => $appliedBlessings,
        ]);
    }
}
