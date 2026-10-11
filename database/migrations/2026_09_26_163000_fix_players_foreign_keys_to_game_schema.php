<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Восстанавливает ссылки на таблицы текущей схемы game.
     *
     * При прежнем восстановлении БД внешние ключи players были созданы с
     * привязкой к временной схеме players_fix_staging. Из-за этого смена расы
     * и любые новые игроки могли завершаться ошибкой внешнего ключа.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropForeign('players_user_id_foreign');
            $table->dropForeign('players_race_id_foreign');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('race_id')->references('id')->on('races');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropForeign('players_user_id_foreign');
            $table->dropForeign('players_race_id_foreign');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('race_id')->references('id')->on('races');
        });
    }
};
