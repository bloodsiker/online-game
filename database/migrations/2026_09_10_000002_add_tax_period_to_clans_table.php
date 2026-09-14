<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clans', function (Blueprint $table): void {
            $table->timestamp('tax_paid_until')->nullable()->after('treasury');
            $table->timestamp('tax_penalties_applied_at')->nullable()->after('tax_paid_until');
            $table->index(['tax_paid_until', 'tax_penalties_applied_at'], 'clans_tax_status_index');
        });

        Schema::create('clan_tax_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->timestamp('paid_from');
            $table->timestamp('paid_until');
            $table->timestamps();

            $table->index(['clan_id', 'created_at']);
        });

        DB::table('clans')->whereNull('tax_paid_until')->update([
            'tax_paid_until' => now()->addMonthNoOverflow(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_tax_payments');

        Schema::table('clans', function (Blueprint $table): void {
            $table->dropIndex('clans_tax_status_index');
            $table->dropColumn(['tax_paid_until', 'tax_penalties_applied_at']);
        });
    }
};
