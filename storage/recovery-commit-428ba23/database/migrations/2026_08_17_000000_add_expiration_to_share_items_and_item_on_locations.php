<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->unsignedInteger('expire')
                ->nullable()
                ->after('count_use')
                ->comment('Срок жизни предмета на локации в минутах');
        });

        Schema::table('item_on_locations', function (Blueprint $table) {
            $table->timestamp('expires_at')
                ->nullable()
                ->after('dungeon_session_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('item_on_locations', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });

        Schema::table('share_items', function (Blueprint $table) {
            $table->dropColumn('expire');
        });
    }
};
