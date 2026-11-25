<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\BossKnowledgeManual;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\PlayerSecretKnowledge;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\SecretKnowledgeSettings;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\SecretKnowledgeStudy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SecretKnowledgeService
{
    public function __construct(private readonly ChatService $chat) {}

    public function settings(): ?SecretKnowledgeSettings
    {
        return SecretKnowledgeSettings::query()->where('is_active', true)->with(['npc', 'bundle'])->first();
    }

    public function atNpc(User $user, int $npcId): bool
    {
        $settings = $this->settings();

        return $settings?->npc_id !== null
            && (int) $settings->npc_id === $npcId
            && (int) $settings->npc?->location_id === (int) $user->location_id;
    }

    public function points(User $user): int
    {
        return (int) PlayerSecretKnowledge::query()->where('player_id', $user->player_id)->value('points');
    }

    public function bundleCount(User $user): int
    {
        $shareItemId = $this->settings()?->bundle_share_item_id;
        if ($shareItemId === null) {
            return 0;
        }

        return (int) Backpack::query()
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->whereHas('item', fn ($query) => $query->where('share_item_id', $shareItemId))
            ->sum('count');
    }

    /** @return array{attempted: int, successes: int, gained: int, points: int} */
    public function analyzeBundles(User $user, int $npcId, int $quantity): array
    {
        $quantity = max(1, min($quantity, 100));

        return DB::transaction(function () use ($user, $npcId, $quantity): array {
            $settings = $this->requireNpc($user, $npcId);
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $bundle = $settings->bundle_share_item_id;
            if ($bundle === null) {
                throw ValidationException::withMessages(['bundle' => __('secret_knowledge.not_configured')]);
            }

            $backpacks = Backpack::query()
                ->where('user_id', $user->id)
                ->where('equipped', 0)
                ->whereHas('item', fn ($query) => $query->where('share_item_id', $bundle))
                ->lockForUpdate()
                ->orderBy('id')
                ->get();
            if ($backpacks->sum('count') < $quantity) {
                throw ValidationException::withMessages(['bundle' => __('secret_knowledge.not_enough_bundles')]);
            }

            $remaining = $quantity;
            foreach ($backpacks as $backpack) {
                if ($remaining === 0) {
                    break;
                }
                $used = min($remaining, (int) $backpack->count);
                $this->consume($backpack, $used);
                $remaining -= $used;
            }

            $successes = 0;
            $gained = 0;
            for ($attempt = 0; $attempt < $quantity; $attempt++) {
                if (random_int(1, 100) <= $settings->analysis_success_chance) {
                    $successes++;
                    $gained += random_int($settings->knowledge_gain_min, $settings->knowledge_gain_max);
                }
            }

            $knowledge = PlayerSecretKnowledge::query()->where('player_id', $player->id)->first();
            if ($gained > 0) {
                $knowledge ??= PlayerSecretKnowledge::query()->create(['player_id' => $player->id, 'points' => 0]);
                $knowledge->increment('points', $gained);
                $knowledge->refresh();
            }

            return ['attempted' => $quantity, 'successes' => $successes, 'gained' => $gained, 'points' => (int) ($knowledge?->points ?? 0)];
        });
    }

    /** Сколько Финко изучает один ворох. */
    public const STUDY_SECONDS = 60;

    /** Сколько после окончания таймера можно забрать результат, прежде чем попытка сгорит. */
    private const STUDY_CLAIM_SECONDS = 120;

    /**
     * Финко начинает изучать один ворох; результат — через STUDY_SECONDS (см. completeStudy).
     *
     * @return array<string, mixed>
     */
    public function startStudy(User $user, int $npcId): array
    {
        return DB::transaction(function () use ($user, $npcId): array {
            $settings = $this->requireNpc($user, $npcId);
            User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($settings->bundle_share_item_id === null) {
                throw ValidationException::withMessages(['bundle' => __('secret_knowledge.not_configured')]);
            }

            $study = SecretKnowledgeStudy::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if ($study !== null && $study->isRunning()) {
                return ['ok' => true, ...$this->studyState($study)];
            }
            if ($this->bundleCount($user) < 1) {
                throw ValidationException::withMessages(['bundle' => __('secret_knowledge.not_enough_bundles')]);
            }

            $study ??= new SecretKnowledgeStudy(['user_id' => $user->id]);
            $study->fill([
                'npc_id' => $npcId,
                'token' => (string) Str::uuid(),
                'started_at' => now(),
                'completes_at' => now()->addSeconds(self::STUDY_SECONDS),
                'expires_at' => now()->addSeconds(self::STUDY_SECONDS + self::STUDY_CLAIM_SECONDS),
            ])->save();

            return ['ok' => true, ...$this->studyState($study)];
        });
    }

    /**
     * Таймер истёк: списываем ворох, бросаем шанс; при находке — личное сообщение в чат.
     *
     * @return array{ok: bool, found: bool, gained: int, points: int, bundles: int, message: string}
     */
    public function completeStudy(User $user, int $npcId, string $token): array
    {
        $result = DB::transaction(function () use ($user, $npcId, $token): array {
            $study = SecretKnowledgeStudy::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if ($study === null || ! hash_equals((string) $study->token, $token)) {
                throw ValidationException::withMessages(['study' => __('secret_knowledge.no_study')]);
            }
            if ($study->completes_at->isFuture()) {
                throw ValidationException::withMessages(['study' => __('secret_knowledge.study_too_early')]);
            }
            $study->delete();
            if ($study->expires_at->isPast()) {
                return ['expired' => true];
            }

            return $this->analyzeBundles($user, $npcId, 1);
        });

        if ($result['expired'] ?? false) {
            return ['ok' => false, 'found' => false, 'gained' => 0, 'points' => $this->points($user), 'bundles' => $this->bundleCount($user), 'message' => __('secret_knowledge.study_expired')];
        }

        $found = $result['gained'] > 0;
        if ($found) {
            $this->chat->sendLootToUser($user, new LocalizedChatMessage('chat.secret_knowledge_found', [
                'gained' => $result['gained'],
                'points' => $result['points'],
            ]));
        }

        return [
            'ok' => true,
            'found' => $found,
            'gained' => $result['gained'],
            'points' => $result['points'],
            'bundles' => $this->bundleCount($user),
            'message' => $found
                ? __('secret_knowledge.analysis_success', ['gained' => $result['gained'], 'points' => number_format($result['points'], 0, '', ' ')])
                : __('secret_knowledge.analysis_fail'),
        ];
    }

    public function cancelStudy(User $user, string $token): void
    {
        SecretKnowledgeStudy::query()->where('user_id', $user->id)->where('token', $token)->delete();
    }

    /** @return array<string, mixed>|null */
    public function activeStudy(User $user): ?array
    {
        $study = SecretKnowledgeStudy::query()->where('user_id', $user->id)->first();

        return $study !== null && $study->isRunning() ? $this->studyState($study) : null;
    }

    /** @return array{token: string, server_time: int, started_at: int, completes_at: int, expires_at: int} */
    private function studyState(SecretKnowledgeStudy $study): array
    {
        return [
            'token' => (string) $study->token,
            'server_time' => now()->getTimestampMs(),
            'started_at' => $study->started_at->getTimestampMs(),
            'completes_at' => $study->completes_at->getTimestampMs(),
            'expires_at' => $study->expires_at->getTimestampMs(),
        ];
    }

    public function studyManual(User $user, int $npcId, int $manualId): BossKnowledgeManual
    {
        return DB::transaction(function () use ($user, $npcId, $manualId): BossKnowledgeManual {
            $this->requireNpc($user, $npcId);
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $manual = BossKnowledgeManual::query()->whereKey($manualId)->where('is_active', true)->lockForUpdate()->firstOrFail();

            if ($this->points($user) < $manual->required_knowledge) {
                throw ValidationException::withMessages(['manual' => __('secret_knowledge.insufficient_points')]);
            }

            $alreadyStudied = DB::table('player_studied_boss_manuals')
                ->where('player_id', $player->id)
                ->where('boss_knowledge_manual_id', $manual->id)
                ->exists();
            if ($alreadyStudied) {
                throw ValidationException::withMessages(['manual' => __('secret_knowledge.already_studied')]);
            }

            $backpack = Backpack::query()
                ->where('user_id', $user->id)
                ->where('equipped', 0)
                ->whereHas('item', fn ($query) => $query->where('share_item_id', $manual->manual_share_item_id))
                ->lockForUpdate()
                ->first();
            if ($backpack === null) {
                throw ValidationException::withMessages(['manual' => __('secret_knowledge.manual_not_owned')]);
            }

            $this->consume($backpack, 1);
            DB::table('player_studied_boss_manuals')->insert([
                'player_id' => $player->id,
                'boss_knowledge_manual_id' => $manual->id,
                'studied_at' => now(),
            ]);

            return $manual->load(['monster', 'bossPart']);
        });
    }

    /** Выдаёт личную часть всем членам победившей группы, кто изучил пособие босса. */
    public function awardBossPart(
        int $battleId,
        int $monsterOnLocationId,
        int $monsterId,
        User $lootOwner,
        ?int $lootPartyId,
    ): void {
        $manual = BossKnowledgeManual::query()
            ->where('monster_id', $monsterId)
            ->where('is_active', true)
            ->with('bossPart')
            ->first();
        if ($manual === null || $manual->bossPart === null) {
            return;
        }

        // Одиночка-победитель по урону получает сам; если по урону победила группа — её участники,
        // но только те, кто действительно бил этого босса в этом бою (ушедшие в другие локации не получают).
        $recipients = $lootPartyId === null
            ? collect([$lootOwner])
            : app(PartyRepositoryInterface::class)->getMemberUsers($lootPartyId);
        $attackerIds = DB::table('battle_monster_damage')
            ->where('battle_id', $battleId)
            ->where('location_monster_id', $monsterOnLocationId)
            ->where('damage', '>', 0)
            ->distinct()
            ->pluck('user_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        if ($lootPartyId !== null) {
            $recipients = $recipients->filter(static fn (User $member): bool => in_array((int) $member->id, $attackerIds, true))->values();
        }
        if ($recipients->isEmpty()) {
            return;
        }

        $learnedPlayerIds = DB::table('player_studied_boss_manuals')
            ->where('boss_knowledge_manual_id', $manual->id)
            ->whereIn('player_id', $recipients->pluck('player_id')->filter()->all())
            ->pluck('player_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        if ($learnedPlayerIds === []) {
            return;
        }

        /** @var ShareItem $part */
        $part = $manual->bossPart;
        foreach ($recipients as $recipient) {
            if (! in_array((int) $recipient->player_id, $learnedPlayerIds, true)) {
                continue;
            }

            $granted = DB::transaction(function () use ($battleId, $monsterOnLocationId, $recipient, $part): bool {
                $claimId = DB::table('boss_knowledge_reward_claims')->insertOrIgnore([
                    'battle_id' => $battleId,
                    'monster_on_location_id' => $monsterOnLocationId,
                    'user_id' => $recipient->id,
                    'share_item_id' => $part->id,
                    'created_at' => now(),
                ]);
                if ($claimId === 0) {
                    return false;
                }

                app(BackpackService::class)
                    ->addItemByShareItem($recipient, $part, 1);

                return true;
            });
            if ($granted) {
                DB::afterCommit(fn () => $this->chat->sendLootToUser(
                    $recipient,
                    new LocalizedChatMessage('chat.secret_knowledge_boss_part', ['item_id' => (int) $part->id]),
                ));
            }
        }
    }

    /** @return array{points: int, bundleCount: int, manuals: Collection<int, BossKnowledgeManual>} */
    public function pageData(User $user): array
    {
        return [
            'points' => $this->points($user),
            'bundleCount' => $this->bundleCount($user),
            'manuals' => BossKnowledgeManual::query()
                ->where('is_active', true)
                ->with(['manual', 'monster', 'bossPart'])
                ->orderBy('required_knowledge')
                ->get()
                ->each(function (BossKnowledgeManual $manual) use ($user): void {
                    $manual->setAttribute('is_studied', DB::table('player_studied_boss_manuals')
                        ->where('player_id', $user->player_id)
                        ->where('boss_knowledge_manual_id', $manual->id)->exists());
                    $manual->setAttribute('in_backpack', Backpack::query()
                        ->where('user_id', $user->id)->where('equipped', 0)
                        ->whereHas('item', fn ($query) => $query->where('share_item_id', $manual->manual_share_item_id))
                        ->exists());
                }),
        ];
    }

    /** Списывает предметы из стопки; последний экземпляр удаляет вместе с самим предметом, если на него больше нет ссылок. */
    private function consume(Backpack $backpack, int $quantity): void
    {
        if ((int) $backpack->count > $quantity) {
            $backpack->decrement('count', $quantity);

            return;
        }

        $itemId = (int) $backpack->item_id;
        $backpack->delete();
        if (! Backpack::query()->where('item_id', $itemId)->exists()) {
            Item::query()->whereKey($itemId)->delete();
        }
    }

    private function requireNpc(User $user, int $npcId): SecretKnowledgeSettings
    {
        $settings = $this->settings();
        if ($settings === null || (int) $settings->npc_id !== $npcId || (int) $settings->npc?->location_id !== (int) $user->location_id) {
            throw ValidationException::withMessages(['npc' => __('secret_knowledge.wrong_npc')]);
        }

        return $settings;
    }
}
