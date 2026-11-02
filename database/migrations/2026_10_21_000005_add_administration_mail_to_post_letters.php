<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_letters', function (Blueprint $table): void {
            $table->unsignedBigInteger('recipient_user_id')->nullable()->change();
            $table->boolean('is_to_administration')->default(false)->index();
            $table->foreignId('administration_sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('administration_thread_id')->nullable()->constrained('post_letters')->restrictOnDelete();
            $table->string('administration_status', 16)->nullable()->index();
            $table->timestamp('administration_last_message_at')->nullable();
        });
    }

    public function down(): void
    {
        // Не удаляем обращения и не превращаем их в обычную личную почту при откате.
        if (DB::table('post_letters')->whereNull('recipient_user_id')->exists()) {
            throw new RuntimeException('Сначала сохраните и перенесите обращения администрации.');
        }

        Schema::table('post_letters', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('administration_thread_id');
            $table->dropConstrainedForeignId('administration_sender_id');
            $table->dropColumn(['is_to_administration', 'administration_status', 'administration_last_message_at']);
            $table->unsignedBigInteger('recipient_user_id')->nullable(false)->change();
        });
    }
};
