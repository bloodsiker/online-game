<?php

declare(strict_types=1);

use App\Modules\Clan\Application\UseCases\DepositClanTreasury;
use App\Modules\Clan\Application\UseCases\WithdrawClanTreasury;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('ECONOMY_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_economy_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Economy race worker refuses non-isolated database.');
}
[$operation, $userId, $argument, $extra, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
if (in_array($operation, ['deposit', 'withdraw'], true)) {
    $user->load('clanMembership.clan', 'clanMembership.role');
}
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Economy race barrier timed out.');
    }
    usleep(10000);
}
try {
    $result = match ($operation) {
        'deposit' => app(DepositClanTreasury::class)->execute($user, 1, (int) $argument),
        'withdraw' => app(WithdrawClanTreasury::class)->execute($user, 1, (int) $argument),
        'sell' => app(SellItems::class)->execute($user, [(int) $extra => ['selected' => 1, 'count' => (int) $argument]]),
        'purchase' => app(PurchaseCart::class)->execute($user, 2),
        'premium' => app(App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart::class)->execute($user, 3),
        'cart_add' => app(ShopCartService::class)->addItem($user, (int) $extra, (int) $argument)->exists,
        'cart_update' => app(ShopCartService::class)->updateQuantity($user, (int) $extra, (int) $argument)?->exists ?? true,
        'cart_clear' => app(ShopCartService::class)->clearCart($user, (int) $argument) >= 0,
        default => throw new LogicException('Unknown economy race operation.'),
    };
    echo json_encode(['ok' => is_bool($result) ? $result : is_string($result) || $result->ok], JSON_THROW_ON_ERROR);
} catch (ModelNotFoundException $e) {
    if ($operation !== 'cart_update') {
        throw $e;
    }
    echo json_encode(['ok' => false, 'message' => 'Cart line was already checked out.'], JSON_THROW_ON_ERROR);
} catch (QueryException $e) {
    // Never classify a deadlock or a database bug as an expected business rejection.
    throw $e;
} catch (RuntimeException $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
