<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Dungeon\Domain\Events\ClanBossRewardGranted;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRewardReceipt;
use App\Modules\Notification\Application\Services\PlayerNotificationService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\DB;

final readonly class SendClanBossRewardMessage implements ShouldQueueAfterCommit
{
    public function __construct(private ChatService $chatService, private PlayerNotificationService $notifications) {}

    public function handle(ClanBossRewardGranted $event): void
    {
        DB::transaction(function () use ($event): void {
            $receipt = ClanBossRewardReceipt::query()->with('user', 'stageState.run')->lockForUpdate()->find($event->receiptId);
            if ($receipt === null || $receipt->rewards === [] || $receipt->notified_at !== null) {
                return;
            }
            $this->notify($receipt);
            $receipt->update(['notified_at' => now()]);
        }, attempts: 3);
    }

    private function notify(ClanBossRewardReceipt $receipt): void
    {
        $parts = [];
        foreach ($receipt->rewards as $entry) {
            $key = match ($entry['type']) {
                'gold' => 'chat.reward_money',
                'diamond' => 'chat.reward_diamond',
                'experience' => 'chat.reward_exp',
                'clan_experience' => 'chat.reward_clan_experience',
                'item' => $receipt->recipient_type === 'clan' ? 'chat.clan_boss_warehouse_item' : 'chat.clan_boss_personal_item',
                default => null,
            };
            if ($key !== null) {
                $parts[] = new LocalizedChatMessage('chat.clan_boss_reward_part', [
                    'reward' => new LocalizedChatMessage($key, ['amount' => format_money($entry['amount'], $entry['type'] === 'clan_experience' ? 2 : 0), 'item_id' => $entry['item_id']]),
                ]);
            }
        }
        $message = new LocalizedChatMessage($receipt->recipient_type === 'clan' ? 'chat.clan_boss_clan_reward' : 'chat.clan_boss_personal_reward', [
            'boss' => ['translations' => $receipt->boss_name], 'rewards' => $parts,
        ]);

        if ($receipt->recipient_type === 'clan') {
            $this->chatService->sendClanNotice((int) $receipt->stageState->run->clan_id, $message);
        } elseif ($receipt->user !== null) {
            $queued = $this->notifications->notify(
                userId: (int) $receipt->user_id,
                type: 'clan_boss_reward',
                deduplicationKey: 'clan_boss_reward:'.$receipt->id,
                message: $message,
            );
            if (! $queued) {
                $this->chatService->sendSystemToUser($receipt->user, $message);
            }
        }
    }
}
