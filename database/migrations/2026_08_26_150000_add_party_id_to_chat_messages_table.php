<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->foreignId('party_id')
                ->nullable()
                ->after('map_id')
                ->constrained('parties')
                ->nullOnDelete();

            $table->index(['channel', 'party_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->dropIndex(['channel', 'party_id', 'id']);
            $table->dropForeign(['party_id']);
            $table->dropColumn('party_id');
        });
    }
};
