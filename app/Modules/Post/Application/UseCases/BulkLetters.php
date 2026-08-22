<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class BulkLetters
{
    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    /**
     * Массовые действия над выбранными письмами: claim (забрать ценности),
     * delete (удалить), claim_delete (забрать и удалить).
     * Забрать ценности может только получатель; удаление — каждая сторона у себя.
     */
    public function execute(User $user, array $letterIds, string $action): int
    {
        $letterIds = array_values(array_filter(array_map('intval', $letterIds)));

        if ($letterIds === [] || ! in_array($action, ['claim', 'delete', 'claim_delete'], strict: true)) {
            return 0;
        }

        $letters = PostLetter::query()
            ->with('shareItem')
            ->whereIn('id', $letterIds)
            ->where(fn ($q) => $q
                ->where(fn ($q2) => $q2->where('recipient_user_id', $user->id)->whereNull('recipient_deleted_at'))
                ->orWhere(fn ($q2) => $q2->where('sender_user_id', $user->id)->whereNull('sender_deleted_at')))
            ->get();

        $affected = 0;

        DB::transaction(function () use ($letters, $user, $action, &$affected): void {
            foreach ($letters as $letter) {
                $isRecipient = $letter->recipient_user_id === $user->id && $letter->recipient_deleted_at === null;

                if ($isRecipient && in_array($action, ['claim', 'claim_delete'], strict: true)) {
                    if ($letter->money > 0 && $letter->money_claimed_at === null) {
                        $user->increment('money', $letter->money);
                        $letter->money_claimed_at = now();
                    }

                    if ($letter->share_item_id !== null && $letter->item_claimed_at === null && $letter->shareItem) {
                        $this->backpackService->giveItemsByShareItem($user, $letter->shareItem, max(1, (int) $letter->item_amount));
                        $letter->item_claimed_at = now();
                    }
                }

                if (in_array($action, ['delete', 'claim_delete'], strict: true)) {
                    if ($isRecipient) {
                        $letter->recipient_deleted_at = now();
                    } else {
                        $letter->sender_deleted_at = now();
                    }
                }

                if ($letter->isDirty()) {
                    $letter->save();
                    $affected++;
                }
            }
        });

        return $affected;
    }
}
