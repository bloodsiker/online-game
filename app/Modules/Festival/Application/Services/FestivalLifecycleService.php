<?php

declare(strict_types=1);

namespace App\Modules\Festival\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Festival\Domain\Enums\FestivalSide;
use App\Modules\Festival\Infrastructure\Persistence\Models\Festival;
use App\Modules\Festival\Infrastructure\Persistence\Models\FestivalParticipant;
use App\Modules\Festival\Infrastructure\Persistence\Models\FestivalSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Жизненный цикл фестиваля, не зависящий от действий игроков: запускается планировщиком.
 * Каждый переход идемпотентен (статус меняется под блокировкой строки) и объявляется в чат один раз.
 */
final class FestivalLifecycleService
{
    /** На случай долгого простоя планировщика: не больше стольких переходов за один запуск. */
    private const MAX_STEPS = 6;

    public function __construct(private readonly ChatService $chat) {}

    /** @return array{started: int, rewards: int, finished: int} */
    public function tick(CarbonInterface $now): array
    {
        $stats = ['started' => 0, 'rewards' => 0, 'finished' => 0];
        $settings = FestivalSettings::query()->where('is_active', true)->first();
        if ($settings === null) {
            return $stats;
        }

        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $changed = DB::transaction(function () use ($settings, $now, &$stats): bool {
                return $this->step($settings, $now, $stats);
            });
            if (! $changed) {
                break;
            }
        }

        return $stats;
    }

    /** @param array{started: int, rewards: int, finished: int} $stats */
    private function step(FestivalSettings $settings, CarbonInterface $now, array &$stats): bool
    {
        $current = Festival::query()
            ->where('status', '!=', Festival::STATUS_FINISHED)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($current === null) {
            if ($settings->launch_at !== null && $now->lessThan($settings->launch_at)) {
                return false;
            }

            $this->start($settings, $now);
            $stats['started']++;

            return true;
        }

        if ($current->status === Festival::STATUS_COLLECTION && $now->greaterThanOrEqualTo($current->collection_ends_at)) {
            $this->tally($current, $now);
            $current->status === Festival::STATUS_REWARDS ? $stats['rewards']++ : $stats['finished']++;

            return true;
        }

        if ($current->status === Festival::STATUS_REWARDS && $now->greaterThanOrEqualTo($current->ends_at)) {
            $this->finish($current, $now);
            $stats['finished']++;

            return true;
        }

        return false;
    }

    private function start(FestivalSettings $settings, CarbonInterface $now): void
    {
        $collectionEnds = $now->copy()->addDays($settings->collection_days);
        Festival::query()->create([
            'status' => Festival::STATUS_COLLECTION,
            'starts_at' => $now,
            'collection_ends_at' => $collectionEnds,
            'ends_at' => $collectionEnds->copy()->addDays($settings->rewards_days),
            'collection_announced_at' => $now,
        ]);

        $this->chat->sendSystem(new LocalizedChatMessage('chat.festival_collection_started', [
            'days' => $settings->collection_days,
        ]));
    }

    /** Конец первого этапа: победитель — сторона с большим числом очков; при равенстве (в т.ч. 0:0) — перезапуск. */
    private function tally(Festival $festival, CarbonInterface $now): void
    {
        $sums = FestivalParticipant::query()
            ->where('festival_id', $festival->id)
            ->selectRaw('side, SUM(points) AS total')
            ->groupBy('side')
            ->pluck('total', 'side');
        $light = (int) ($sums[FestivalSide::Light->value] ?? 0);
        $dark = (int) ($sums[FestivalSide::Dark->value] ?? 0);

        $festival->light_points = $light;
        $festival->dark_points = $dark;

        if ($light === $dark) {
            $festival->status = Festival::STATUS_FINISHED;
            $festival->outcome = Festival::OUTCOME_DRAW;
            $festival->finished_announced_at = $now;
            $festival->save();
            $this->chat->sendSystem(new LocalizedChatMessage('chat.festival_draw'));

            return;
        }

        $winner = $light > $dark ? FestivalSide::Light : FestivalSide::Dark;
        $festival->status = Festival::STATUS_REWARDS;
        $festival->outcome = Festival::OUTCOME_WINNER;
        $festival->winner_side = $winner->value;
        $festival->rewards_announced_at = $now;
        $festival->save();

        $this->chat->sendSystem(new LocalizedChatMessage('chat.festival_rewards_started', [
            'side' => new LocalizedChatMessage('chat.festival_side_'.$winner->value),
        ]));
    }

    private function finish(Festival $festival, CarbonInterface $now): void
    {
        $festival->status = Festival::STATUS_FINISHED;
        $festival->finished_announced_at = $now;
        $festival->save();

        $this->chat->sendSystem(new LocalizedChatMessage('chat.festival_finished'));
    }
}
