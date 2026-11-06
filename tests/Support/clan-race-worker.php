<?php

declare(strict_types=1);

use App\Modules\Clan\Application\UseCases\PayClanTax;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = getenv('CLAN_RACE_TEST_DATABASE') ?: '';
if (! preg_match('/^codex_clan_race_[a-z0-9_]+$/D', $database)
    || DB::connection()->getDriverName() !== 'mysql'
    || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Clan race worker refuses non-isolated database.');
}
[$operation, $userId, $argument, $extra, $barrier, $workerId] = array_slice($argv, 1);
$user = User::without('player')->findOrFail((int) $userId);
// Intentionally cache the old permissions/membership before competing requests run.
$user->load('clanMembership.role', 'clanMembership.clan');
$request = in_array($operation, ['accept', 'withdraw', 'cancel'], true) ? ClanJoinRequest::findOrFail((int) $argument) : null;
file_put_contents($barrier.'/ready-'.$workerId, 'ready');
$deadline = microtime(true) + 10;
while (! is_file($barrier.'/start')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Clan race barrier timed out.');
    }
    usleep(10000);
}
try {
    $service = app(ClanService::class);
    config(['game.clan_monthly_tax' => 50]);
    $error = null;
    match ($operation) {
        'accept_invite' => $service->acceptInvite($user, $argument),
        'decline_invite' => $service->declineInvite($user, $argument),
        'accept' => $service->acceptApplication($user, $request),
        'withdraw' => $service->withdrawApplication($user, $request),
        'cancel' => $service->inviteCancel($user, $request),
        'apply' => $service->applyToClan($user, Clan::findOrFail((int) $argument)),
        'invite' => $service->invite($user, $argument),
        'leave' => $service->leaveClan($user),
        'tax' => app(PayClanTax::class)->execute($user, 1),
        'transfer' => $service->saveMemberRoles($user, [(int) $userId => ['grade' => 2], (int) $argument => ['grade' => 1]]),
        'assign' => $service->saveMemberRoles($user, [(int) $argument => ['grade' => (int) $extra]]),
        'delete_role' => $service->deleteRole($user, ClanRole::findOrFail((int) $argument)),
        'revoke' => $service->saveRoles($user, [(int) $argument => ['flags' => []]]),
        'learn' => $error = app(ClanSkillService::class)->learn($user->clanMembership->clan, ClanSkillDefinition::findOrFail((int) $argument), $user->player, $user, (int) $extra),
        default => throw new LogicException('Unknown clan race operation.'),
    };
    echo json_encode(['ok' => $error === null, 'message' => $error], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (QueryException $exception) {
    // Deadlocks, unique failures and schema errors must fail the test, not count as business rejections.
    throw $exception;
} catch (RuntimeException $exception) {
    echo json_encode(['ok' => false, 'message' => $exception->getMessage()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
