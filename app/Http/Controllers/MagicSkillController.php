<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\AttackResultDTO;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Player\Player;
use App\Services\Combat\BattleEffectService;
use App\Services\PlayerStatService;
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
                ->where('last_online_at', '>=', now()->subMinutes(5))
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

        if ($caster->mp_now < $skill->mana_cost) {
            return response()->json(['status' => 'error', 'message' => sprintf('Недостаточно маны. Нужно: %d MP', $skill->mana_cost)], 422);
        }

        // Определяем цель
        $targetId = $request->input('target_player_id');
        $target   = $targetId ? Player::find($targetId) : $caster;

        if (!$target) {
            return response()->json(['status' => 'error', 'message' => 'Цель не найдена'], 404);
        }

        // Снимаем ману с кастера
        $caster->mp_now -= $skill->mana_cost;

        $log = new AttackResultDTO();

        // Лечение
        if ($skill->base_healing > 0) {
            $heal = $skill->base_healing;
            $target->hp_now = min($target->hp_max, $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }

        // Эффекты из skillEffects (с проверкой шанса)
        $skill->load('skillEffects');
        foreach ($skill->skillEffects as $effect) {
            if (random_int(1, 100) <= $effect->pivot->chance) {
                $this->effectService->applyEffectToPlayer($effect, $target, null, $log);
            }
        }

        $caster->save();
        if ($target->id !== $caster->id) {
            $target->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => $log->getLog() ?: sprintf('Применено: «%s»', $skill->name),
            'mp_now'  => $caster->mp_now,
        ]);
    }
}