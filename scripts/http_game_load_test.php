<?php

declare(strict_types=1);

/**
 * Concurrent HTTP load test for login -> real monster fight -> fountain heal.
 * Uses one process and one independent cookie session per virtual user.
 *
 * php scripts/http_game_load_test.php \
 *   --fixture=storage/app/load-test/fixture.json \
 *   --confirm-database=game_load_test --stages=1,5,10,20,50 --iterations=2
 */
$basePath = dirname(__DIR__);
$options = getopt('', [
    'fixture::',
    'confirm-database:',
    'base-url::',
    'stages::',
    'iterations::',
    'timeout::',
    'stop-error-rate::',
    'output::',
]);

$fixturePath = (string) ($options['fixture'] ?? $basePath.'/storage/app/load-test/fixture.json');
$baseUrl = rtrim((string) ($options['base-url'] ?? 'http://127.0.0.1:85'), '/');
$stages = array_values(array_filter(array_map('intval', explode(',', (string) ($options['stages'] ?? '1,5,10,20,50')))));
$iterations = max(1, min(50, (int) ($options['iterations'] ?? 2)));
$timeout = max(2, min(120, (int) ($options['timeout'] ?? 30)));
$stopErrorRate = max(0.0, min(100.0, (float) ($options['stop-error-rate'] ?? 5.0)));
$timestamp = date('Ymd_His');
$output = (string) ($options['output'] ?? $basePath."/storage/app/load-test/http-load-{$timestamp}.json");

if (! extension_loaded('curl') || ! extension_loaded('pcntl')) {
    throw new RuntimeException('The curl and pcntl PHP extensions are required.');
}
if (! is_file($fixturePath)) {
    throw new RuntimeException("Fixture not found: {$fixturePath}");
}

$fixture = json_decode((string) file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
$accounts = $fixture['accounts'] ?? [];
if (($fixture['database'] ?? null) !== 'game_load_test') {
    throw new RuntimeException('Fixture was not created from game_load_test.');
}
if (($options['confirm-database'] ?? null) !== 'game_load_test') {
    throw new RuntimeException('Pass --confirm-database=game_load_test after verifying the active Laravel database.');
}
if ($stages === [] || max($stages) > count($accounts)) {
    throw new RuntimeException('Not enough fixture accounts for requested stages.');
}
if (count($stages) * $iterations > (int) ($fixture['monsters_per_user'] ?? 0)) {
    throw new RuntimeException('Not enough monster instances per user for all stages.');
}

function httpRequest(CurlHandle $curl, string $url, string $label, int $expectedStatus, ?array $post = null): array
{
    curl_setopt($curl, CURLOPT_URL, $url);
    if ($post === null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, null);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $body = curl_exec($curl);
    $curlError = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $durationMs = round((float) curl_getinfo($curl, CURLINFO_TOTAL_TIME) * 1000, 3);
    $body = is_string($body) ? $body : '';
    $ok = $curlError === '' && $status === $expectedStatus;

    return [
        'body' => $body,
        'sample' => [
            'label' => $label,
            'duration_ms' => $durationMs,
            'status' => $status,
            'bytes' => strlen($body),
            'ok' => $ok,
        ],
        'error' => $ok ? null : ($curlError !== '' ? $curlError : "unexpected HTTP {$status}, expected {$expectedStatus}"),
    ];
}

function runVirtualUser(array $account, int $monsterOffset, int $iterations, string $baseUrl, int $timeout, int $fountainId): array
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEFILE => '',
        CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => ['Accept: text/html', 'User-Agent: OnlineGameLoadTest/1.0'],
    ]);

    $result = [
        'samples' => [],
        'errors' => [],
        'fights_started' => 0,
        'fights_finished' => 0,
        'wins' => 0,
        'losses' => 0,
        'combat_rounds' => 0,
        'heals' => 0,
    ];

    $record = static function (array $response) use (&$result): string {
        $result['samples'][] = $response['sample'];
        if ($response['error'] !== null) {
            $result['errors'][] = $response['sample']['label'].': '.$response['error'];
        }

        return $response['body'];
    };

    $login = httpRequest($curl, $baseUrl.'/login', 'login', 302, [
        'email' => $account['email'],
        'password' => $account['password'],
    ]);
    $record($login);
    if (! $login['sample']['ok']) {
        curl_close($curl);

        return $result;
    }

    for ($iteration = 0; $iteration < $iterations; $iteration++) {
        $monsterId = $account['monster_instance_ids'][$monsterOffset + $iteration] ?? null;
        if ($monsterId === null) {
            $result['errors'][] = 'fixture: missing monster instance';
            break;
        }

        $start = httpRequest($curl, $baseUrl.'/fight/attack/monster/'.$monsterId, 'fight_start', 200);
        $body = $record($start);
        if (! $start['sample']['ok']) {
            continue;
        }
        $result['fights_started']++;

        if (! preg_match("/actionAttack\\('([0-9]+)',\\s*'([0-9]+)',\\s*0\\)/", $body, $match)) {
            $result['errors'][] = 'fight_start: attack action not found';

            continue;
        }

        $battleId = (int) $match[1];
        $targetId = (int) $match[2];
        $finished = false;

        for ($round = 1; $round <= 100; $round++) {
            $attack = httpRequest(
                $curl,
                $baseUrl."/fight/attack/{$battleId}/{$targetId}/0",
                'fight_attack',
                200,
            );
            $body = $record($attack);
            if (! $attack['sample']['ok']) {
                break;
            }

            $result['combat_rounds']++;
            if (str_contains($body, 'проиграли')) {
                $result['losses']++;
                $result['fights_finished']++;
                $finished = true;
                break;
            }
            if (str_contains($body, 'id="finish-fight"') || ! str_contains($body, 'actionAttack(')) {
                $result['wins']++;
                $result['fights_finished']++;
                $finished = true;
                break;
            }
        }

        if (! $finished) {
            $result['errors'][] = "fight {$battleId}: did not finish cleanly";
        }

        $heal = httpRequest($curl, $baseUrl.'/heal/'.$fountainId, 'heal', 200);
        $record($heal);
        if ($heal['sample']['ok']) {
            $result['heals']++;
        }
    }

    curl_close($curl);

    return $result;
}

function percentile(array $values, float $percentile): ?float
{
    if ($values === []) {
        return null;
    }
    sort($values, SORT_NUMERIC);
    $index = (int) ceil(($percentile / 100) * count($values)) - 1;

    return round((float) $values[max(0, min(count($values) - 1, $index))], 3);
}

function summarizeStage(int $concurrency, int $iterations, float $wallSeconds, array $children): array
{
    $samples = [];
    $errors = [];
    $counters = [
        'fights_started' => 0,
        'fights_finished' => 0,
        'wins' => 0,
        'losses' => 0,
        'combat_rounds' => 0,
        'heals' => 0,
    ];

    foreach ($children as $child) {
        array_push($samples, ...($child['samples'] ?? []));
        array_push($errors, ...($child['errors'] ?? []));
        foreach (array_keys($counters) as $counter) {
            $counters[$counter] += (int) ($child[$counter] ?? 0);
        }
    }

    $latencies = array_map(static fn (array $sample): float => (float) $sample['duration_ms'], $samples);
    $failedRequests = count(array_filter($samples, static fn (array $sample): bool => ! $sample['ok']));
    $endpoints = [];
    foreach ($samples as $sample) {
        $label = $sample['label'];
        $endpoints[$label] ??= ['requests' => 0, 'errors' => 0, 'durations' => []];
        $endpoints[$label]['requests']++;
        $endpoints[$label]['errors'] += $sample['ok'] ? 0 : 1;
        $endpoints[$label]['durations'][] = (float) $sample['duration_ms'];
    }
    foreach ($endpoints as &$endpoint) {
        $endpoint['avg_ms'] = round(array_sum($endpoint['durations']) / max(1, count($endpoint['durations'])), 3);
        $endpoint['p95_ms'] = percentile($endpoint['durations'], 95);
        $endpoint['max_ms'] = round(max($endpoint['durations']), 3);
        unset($endpoint['durations']);
    }
    unset($endpoint);

    return [
        'concurrency' => $concurrency,
        'iterations_per_user' => $iterations,
        'wall_seconds' => round($wallSeconds, 3),
        'requests' => count($samples),
        'request_rps' => round(count($samples) / max(0.001, $wallSeconds), 3),
        'failed_requests' => $failedRequests,
        'error_rate_percent' => round(100 * $failedRequests / max(1, count($samples)), 3),
        'bytes_received' => array_sum(array_column($samples, 'bytes')),
        'latency_ms' => [
            'min' => $latencies === [] ? null : round(min($latencies), 3),
            'avg' => $latencies === [] ? null : round(array_sum($latencies) / count($latencies), 3),
            'p50' => percentile($latencies, 50),
            'p95' => percentile($latencies, 95),
            'p99' => percentile($latencies, 99),
            'max' => $latencies === [] ? null : round(max($latencies), 3),
        ],
        'endpoints' => $endpoints,
        'gameplay' => $counters + [
            'winrate_percent' => round(100 * $counters['wins'] / max(1, $counters['fights_finished']), 3),
            'fight_throughput_per_second' => round($counters['fights_finished'] / max(0.001, $wallSeconds), 3),
        ],
        'errors' => array_slice($errors, 0, 50),
    ];
}

$report = [
    'started_at' => date(DATE_ATOM),
    'base_url' => $baseUrl,
    'database' => $fixture['database'],
    'fixture_run_id' => $fixture['run_id'],
    'configuration' => [
        'stages' => $stages,
        'iterations_per_user' => $iterations,
        'request_timeout_seconds' => $timeout,
        'stop_error_rate_percent' => $stopErrorRate,
    ],
    'stages' => [],
];

foreach ($stages as $stageIndex => $concurrency) {
    $stageStart = microtime(true);
    $pipes = [];
    $pids = [];
    $monsterOffset = $stageIndex * $iterations;

    for ($userIndex = 0; $userIndex < $concurrency; $userIndex++) {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            throw new RuntimeException('Unable to create IPC socket pair.');
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('Unable to fork virtual user.');
        }
        if ($pid === 0) {
            fclose($pair[0]);
            $childResult = runVirtualUser(
                $accounts[$userIndex],
                $monsterOffset,
                $iterations,
                $baseUrl,
                $timeout,
                (int) $fixture['fountain_structure_id'],
            );
            fwrite($pair[1], json_encode($childResult, JSON_THROW_ON_ERROR));
            fclose($pair[1]);
            exit(0);
        }

        fclose($pair[1]);
        $pipes[$pid] = $pair[0];
        $pids[] = $pid;
    }

    $children = [];
    foreach ($pids as $pid) {
        $payload = stream_get_contents($pipes[$pid]);
        fclose($pipes[$pid]);
        pcntl_waitpid($pid, $status);
        if (! is_string($payload) || $payload === '') {
            $children[] = ['samples' => [], 'errors' => ["virtual user {$pid}: empty result"]];

            continue;
        }
        $children[] = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
    }

    $stage = summarizeStage($concurrency, $iterations, microtime(true) - $stageStart, $children);
    $report['stages'][] = $stage;
    fwrite(STDOUT, json_encode($stage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);

    if ($stage['error_rate_percent'] > $stopErrorRate) {
        $report['stopped_early'] = "Stage {$concurrency}: request error rate exceeded {$stopErrorRate}%";
        break;
    }
}

$report['finished_at'] = date(DATE_ATOM);
$report['summary'] = [
    'stages_completed' => count($report['stages']),
    'requests' => array_sum(array_column($report['stages'], 'requests')),
    'failed_requests' => array_sum(array_column($report['stages'], 'failed_requests')),
    'fights_finished' => array_sum(array_map(static fn (array $stage): int => $stage['gameplay']['fights_finished'], $report['stages'])),
    'combat_rounds' => array_sum(array_map(static fn (array $stage): int => $stage['gameplay']['combat_rounds'], $report['stages'])),
];

$directory = dirname($output);
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    throw new RuntimeException("Unable to create output directory: {$directory}");
}
$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
file_put_contents($output, $json);
file_put_contents($directory.'/http-load-latest.json', $json);

fwrite(STDOUT, json_encode(['report' => $output, 'summary' => $report['summary']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);

exit(($report['summary']['failed_requests'] ?? 0) > 0 ? 2 : 0);
