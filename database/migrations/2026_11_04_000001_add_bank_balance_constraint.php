<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // BankBalanceLimits::canCreditAccount uses the signed 64-bit PHP integer range.
    private const MAX_BALANCE = '9223372036854775807';

    public function up(): void
    {
        $driver = $this->driver();
        $max = self::MAX_BALANCE;
        $invalid = DB::table('users')->whereRaw("bank_balance IS NULL OR bank_balance < 0 OR bank_balance > {$max}");
        if ((clone $invalid)->exists()) {
            throw new RuntimeException('Invalid bank balances: '.$invalid->select(['id', 'bank_balance'])->limit(10)->get()->toJson().'. No rows were changed.');
        }
        if ($driver === 'mysql') {
            $constraint = $this->constraint();
            if ($constraint === null) {
                DB::statement("ALTER TABLE users ADD CONSTRAINT users_bank_balance_range CHECK (bank_balance IS NOT NULL AND bank_balance BETWEEN 0 AND {$max}) ENFORCED");
            } elseif ($constraint->ENFORCED !== 'YES') {
                DB::statement('ALTER TABLE users ALTER CHECK users_bank_balance_range ENFORCED');
            }

            return;
        }
        DB::transaction(function () use ($max): void {
            foreach (['insert', 'update'] as $operation) {
                DB::unprepared("CREATE TRIGGER IF NOT EXISTS users_bank_balance_range_{$operation} BEFORE {$operation} ON users
                    WHEN NEW.bank_balance IS NULL OR NEW.bank_balance < 0 OR NEW.bank_balance > {$max}
                    BEGIN SELECT RAISE(ABORT, 'users_bank_balance_range'); END");
            }
        });
    }

    public function down(): void
    {
        if ($this->driver() === 'mysql') {
            if ($this->constraint() !== null) {
                DB::statement('ALTER TABLE users DROP CHECK users_bank_balance_range');
            }

            return;
        }
        DB::transaction(function (): void {
            foreach (['insert', 'update'] as $operation) {
                DB::unprepared("DROP TRIGGER IF EXISTS users_bank_balance_range_{$operation}");
            }
        });
    }

    private function driver(): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return $driver;
        }
        if ($driver === 'mysql') {
            $version = DB::selectOne('SELECT VERSION() AS version')->version;
            if (stripos($version, 'MariaDB') === false && version_compare($version, '8.0.16', '>=')) {
                return $driver;
            }
        }
        throw new RuntimeException('Bank balance constraint requires MySQL 8.0.16+ or SQLite.');
    }

    private function constraint(): ?object
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'users')->where('CONSTRAINT_NAME', 'users_bank_balance_range')->where('CONSTRAINT_TYPE', 'CHECK')->first();
    }
};
