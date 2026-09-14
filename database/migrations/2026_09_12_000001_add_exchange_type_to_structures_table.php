<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table): void {
            $table->string('exchange_type', 20)->nullable()->after('type')
                ->comment('Механика обмена для type=reputation_exchange: linear|gamble, см. ReputationExchangeStrategy');
        });

        DB::table('structures')
            ->where('type', 'reputation_exchange')
            ->update(['exchange_type' => 'linear']);
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table): void {
            $table->dropColumn('exchange_type');
        });
    }
};
