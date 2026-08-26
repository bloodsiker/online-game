<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_invites', function (Blueprint $table): void {
            $table->foreignId('chat_message_id')
                ->nullable()
                ->after('uuid')
                ->constrained('chat_messages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('party_invites', function (Blueprint $table): void {
            $table->dropForeign(['chat_message_id']);
            $table->dropColumn('chat_message_id');
        });
    }
};
