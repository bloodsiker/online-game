<?php

declare(strict_types=1);

use App\Modules\Post\Application\Services\AdministrationMailService;
use App\Modules\Post\Application\UseCases\BulkLetters;
use App\Modules\Post\Application\UseCases\DeleteLetter;
use App\Modules\Post\Application\UseCases\SendLetter;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('POST_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_post_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Post race worker refuses non-isolated database.');
}
Event::fake([MailboxUnreadStateUpdated::class]);
[$operation, $userId, $argument, $extra, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Post race barrier timed out.');
    }
    usleep(10000);
}
$result = match ($operation) {
    'claim' => ['affected' => app(BulkLetters::class)->execute($user, json_decode($argument, true, flags: JSON_THROW_ON_ERROR), $extra)],
    'delete' => ['affected' => (int) app(DeleteLetter::class)->execute($user, (int) $argument)],
    'admin_reply' => ['id' => app(AdministrationMailService::class)->reply($user, (int) $argument, $extra)->id],
    'send' => ['ok' => app(SendLetter::class)->execute($user, $extra, 'Race', 'Parallel request', (int) $argument)->ok],
    default => throw new RuntimeException('Unknown post race operation.'),
};
echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
