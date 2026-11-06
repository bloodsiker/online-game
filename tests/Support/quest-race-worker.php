<?php

declare(strict_types=1);

use App\Modules\Quest\Application\UseCases\CompleteQuest;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Presentation\Http\QuestController;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\QuestTestDoubles;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('QUEST_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_quest_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Quest race worker refuses non-isolated database.');
}
QuestTestDoubles::bind();
[$operation, $userId, $progressId, $revision, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
$quest = Quest::findOrFail(1);
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Quest race barrier timed out.');
    }
    usleep(10000);
}
try {
    Auth::setUser($user);
    $result = match ($operation) {
        'complete' => app(CompleteQuest::class)->execute($user, $quest, (int) $progressId, (int) $revision),
        'take' => app(QuestController::class)->take(1, Request::create('/quest/1/take', 'POST', ['npc' => 1])),
        'cancel' => app(QuestController::class)->cancelQuest((int) $progressId),
        'cancel_clan' => app(QuestController::class)->cancelClanQuest((int) $progressId, Request::create('/quest/clan/1/cancel', 'POST', ['npc' => 1])),
    };
    echo json_encode(['ok' => true]);
} catch (ValidationException|ModelNotFoundException $exception) {
    echo json_encode(['ok' => false]);
}
