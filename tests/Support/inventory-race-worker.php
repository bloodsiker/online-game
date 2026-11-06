<?php

declare(strict_types=1);

use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('INVENTORY_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_inventory_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Inventory race worker refuses non-isolated database.');
}
[$operation, $userId, $itemId, $extra, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
$item = $operation === 'transfer' ? Item::findOrFail((int) $itemId) : null;
$target = $operation === 'transfer' ? User::without('player')->findOrFail((int) $extra) : null;
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Inventory race barrier timed out.');
    }
    usleep(10000);
}
$service = app(ItemService::class);
$result = match ($operation) {
    'drop' => ['ok' => $service->drop($user, (int) $itemId, (int) $extra) === null],
    'transfer' => ['ok' => $service->handOver($user, $item, $target) === null],
    'pickup' => ['message' => $service->pickUpFromLocation($user, (int) $itemId)],
    'sell' => ['ok' => app(SellItems::class)->execute($user, [(int) $itemId => ['selected' => 1, 'count' => (int) $extra]])->ok],
    default => throw new RuntimeException('Unknown inventory race operation.'),
};
echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
