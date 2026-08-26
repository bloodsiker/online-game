<?php

declare(strict_types=1);

use App\Modules\Party\Infrastructure\Persistence\Models\PartyInvite;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('party_invites')
            ->where('status', PartyInvite::STATUS_PENDING)
            ->whereNull('chat_message_id')
            ->orderBy('id')
            ->each(function (object $invite): void {
                $oldAcceptPath = '/party/accept/'.$invite->id;
                $message = DB::table('chat_messages')
                    ->where('target_user_id', $invite->invited_user_id)
                    ->where('message', 'like', '%'.$oldAcceptPath.'%')
                    ->latest('id')
                    ->first();

                if ($message === null) {
                    return;
                }

                $updatedMessage = str_replace(
                    [$oldAcceptPath, '/party/decline/'.$invite->id],
                    ['/party/accept/'.$invite->uuid, '/party/decline/'.$invite->uuid],
                    $message->message,
                );

                DB::table('chat_messages')->where('id', $message->id)->update(['message' => $updatedMessage]);
                DB::table('party_invites')->where('id', $invite->id)->update(['chat_message_id' => $message->id]);
            });
    }

    public function down(): void
    {
        // UUID-ссылки намеренно не откатываются: старые числовые ссылки небезопасны.
    }
};
