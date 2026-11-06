<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Frozen schema limit; matches WalletBalanceLimits without depending on future application code.
    private const MAX_BALANCE = 2147483647;

    public function up(): void
    {
        $driver = $this->supportedDriver();
        if (! Schema::hasColumns('users', ['id', 'money', 'diamond'])) {
            throw new RuntimeException('Wallet constraints require users.id, users.money and users.diamond. No data was changed.');
        }

        if ($driver === 'sqlite') {
            // SQLite cannot add a CHECK to an existing table. Preserve its FKs, indexes and data.
            DB::transaction(function (): void {
                $this->assertValidBalances();
                foreach (['money', 'diamond'] as $column) {
                    foreach (['insert', 'update'] as $operation) {
                        $name = $this->name($column).'_'.$operation;
                        $max = self::MAX_BALANCE;
                        DB::unprepared("CREATE TRIGGER IF NOT EXISTS `{$name}` BEFORE {$operation} ON `users`
                            WHEN NEW.`{$column}` IS NULL OR NEW.`{$column}` < 0 OR NEW.`{$column}` > {$max}
                            BEGIN SELECT RAISE(ABORT, '{$name}'); END");
                    }
                }
            });

            return;
        }

        // MySQL DDL commits implicitly. Validate both columns before the single atomic ALTER.
        $this->assertValidBalances();
        $clauses = [];
        foreach (['money', 'diamond'] as $column) {
            $name = $this->name($column);
            $existing = $this->mysqlConstraint($name);
            if ($existing === null) {
                $max = self::MAX_BALANCE;
                $clauses[] = "ADD CONSTRAINT `{$name}` CHECK (`{$column}` IS NOT NULL AND `{$column}` BETWEEN 0 AND {$max}) ENFORCED";
            } elseif ($existing->ENFORCED !== 'YES') {
                $clauses[] = "ALTER CHECK `{$name}` ENFORCED";
            }
        }
        if ($clauses !== []) {
            DB::statement('ALTER TABLE `users` '.implode(', ', $clauses));
        }
    }

    public function down(): void
    {
        if ($this->supportedDriver() === 'sqlite') {
            DB::transaction(function (): void {
                foreach (['money', 'diamond'] as $column) {
                    foreach (['insert', 'update'] as $operation) {
                        $name = $this->name($column).'_'.$operation;
                        DB::unprepared("DROP TRIGGER IF EXISTS `{$name}`");
                    }
                }
            });

            return;
        }

        $clauses = [];
        foreach (['money', 'diamond'] as $column) {
            $name = $this->name($column);
            if ($this->mysqlConstraint($name) !== null) {
                $clauses[] = "DROP CHECK `{$name}`";
            }
        }
        if ($clauses !== []) {
            DB::statement('ALTER TABLE `users` '.implode(', ', $clauses));
        }
    }

    private function supportedDriver(): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return $driver;
        }
        if ($driver !== 'mysql') {
            throw new RuntimeException('Wallet constraints support MySQL 8.0.16+ and SQLite only.');
        }
        $version = (string) DB::selectOne('SELECT VERSION() AS version')->version;
        if (stripos($version, 'MariaDB') !== false || version_compare($version, '8.0.16', '<')) {
            throw new RuntimeException("Wallet constraints require enforced CHECK support in MySQL 8.0.16+; got {$version}. No data was changed.");
        }

        return $driver;
    }

    private function assertValidBalances(): void
    {
        $max = self::MAX_BALANCE;
        $invalid = DB::table('users')->whereRaw("money IS NULL OR money < 0 OR money > {$max} OR diamond IS NULL OR diamond < 0 OR diamond > {$max}");
        $count = (clone $invalid)->count();
        if ($count > 0) {
            $sample = $invalid->select(['id', 'money', 'diamond'])->orderBy('id')->limit(10)->get()->toJson();
            throw new RuntimeException("Cannot add wallet constraints: invalid_count={$count}, users={$sample}. No rows were changed. Resolve invalid balances explicitly before retrying.");
        }
    }

    private function name(string $column): string
    {
        return 'users_'.$column.'_wallet_range';
    }

    private function mysqlConstraint(string $name): ?object
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'users')->where('CONSTRAINT_NAME', $name)
            ->where('CONSTRAINT_TYPE', 'CHECK')->first();
    }
};
