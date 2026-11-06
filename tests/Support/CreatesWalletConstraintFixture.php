<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesWalletConstraintFixture
{
    private function prepareWalletConstraintFixture(): void
    {
        $database = (string) getenv('WALLET_CONSTRAINT_TEST_DATABASE');
        if ($database === '') {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
            DB::purge('sqlite');
            $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        } else {
            // Guard before every schema/data mutation; never use a game database.
            $this->assertMatchesRegularExpression('/^codex_wallet_constraint_[a-z0-9_]+$/D', $database);
            $this->assertSame('mysql', DB::connection()->getDriverName());
            $this->assertSame($database, DB::connection()->getDatabaseName());
        }
        Schema::dropIfExists('wallet_test_receipts');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            // Deliberately wider/nullable: the migration must enforce its range, not rely on INT.
            $table->bigInteger('money')->nullable()->default(0);
            $table->bigInteger('diamond')->nullable()->default(0);
        });
        Schema::create('wallet_test_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('amount')->default(0);
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Wallet fixture', 'money' => 10, 'diamond' => 10]);
    }

    private function walletMigration(): Migration
    {
        return require database_path('migrations/2026_11_03_000004_add_wallet_balance_constraints.php');
    }
}
