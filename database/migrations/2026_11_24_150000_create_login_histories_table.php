<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // История входов хранится бессрочно, поэтому без внешнего ключа: запись переживает удаление аккаунта.
        Schema::create('login_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('remembered')->default(false);

            // С сервера, в момент входа.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 40)->nullable();
            $table->string('browser_version', 24)->nullable();
            $table->string('os', 40)->nullable();
            $table->string('os_version', 24)->nullable();
            $table->string('device_type', 16)->nullable();
            $table->string('accept_language', 120)->nullable();
            $table->string('referer', 500)->nullable();

            // От браузера, один раз за сессию (после загрузки игры).
            $table->string('timezone', 64)->nullable();
            $table->string('language', 16)->nullable();
            $table->string('languages', 120)->nullable();
            $table->unsignedSmallInteger('screen_width')->nullable();
            $table->unsignedSmallInteger('screen_height')->nullable();
            $table->unsignedSmallInteger('viewport_width')->nullable();
            $table->unsignedSmallInteger('viewport_height')->nullable();
            $table->decimal('pixel_ratio', 4, 2)->nullable();
            $table->unsignedTinyInteger('touch_points')->nullable();
            $table->string('color_scheme', 8)->nullable();
            $table->string('display_mode', 16)->nullable();
            $table->string('connection_type', 12)->nullable();
            $table->string('connection_kind', 16)->nullable();
            $table->decimal('downlink', 7, 2)->nullable();
            $table->unsignedSmallInteger('rtt')->nullable();
            $table->boolean('save_data')->nullable();
            $table->decimal('device_memory', 5, 2)->nullable();
            $table->unsignedTinyInteger('cpu_cores')->nullable();
            $table->timestamp('client_reported_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'login_histories_user_created_index');
            $table->index('ip_address', 'login_histories_ip_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
