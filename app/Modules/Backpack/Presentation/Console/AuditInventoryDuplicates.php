<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Presentation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class AuditInventoryDuplicates extends Command
{
    protected $signature = 'inventory:audit-duplicates {--json : Print machine-readable results}';

    protected $description = 'Read-only audit before adding unique inventory/ground-placement constraints';

    public function handle(): int
    {
        $report = [];
        foreach (['backpacks' => ['user_id', 'item_id'], 'item_on_locations' => ['item_id', 'location_id']] as $table => $columns) {
            $query = DB::table($table)->select($columns)->selectRaw('COUNT(*) AS rows_count')
                ->groupBy($columns)->havingRaw('COUNT(*) > 1');
            $report[$table] = [
                'duplicate_groups' => DB::query()->fromSub(clone $query, 'duplicates')->count(),
                'examples' => $query->orderBy($columns[0])->orderBy($columns[1])->limit(20)->get()->all(),
            ];
        }
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            foreach ($report as $table => $result) {
                $this->line($table.': '.$result['duplicate_groups'].' duplicate groups.');
            }
            $this->line('Read-only audit: no rows were changed. Ground examples may belong to different dungeon sessions; inspect before adding constraints.');
        }

        return array_sum(array_column($report, 'duplicate_groups')) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
