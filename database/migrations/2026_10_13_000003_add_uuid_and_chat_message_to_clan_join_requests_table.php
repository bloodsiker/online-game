<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clan_join_requests', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('user_id');
            $table->foreignId('chat_message_id')
                ->nullable()
                ->after('uuid')
                ->constrained('chat_messages')
                ->nullOnDelete();
        });

        DB::table('clan_join_requests')->orderBy('id')->each(function (object $request): void {
            DB::table('clan_join_requests')->where('id', $request->id)->update(['uuid' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('clan_join_requests', function (Blueprint $table): void {
            $table->dropForeign(['chat_message_id']);
            $table->dropColumn(['chat_message_id', 'uuid']);
        });
    }
};
