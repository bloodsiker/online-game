<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Scheduler\Application\Services\ScheduledTaskManager;
use App\Modules\Scheduler\Application\Services\ScheduledTaskRegistry;
use App\Modules\Scheduler\Application\Services\ScheduledTaskRunner;
use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class ScheduledTaskController extends Controller
{
    public function index(ScheduledTaskManager $manager): View
    {
        $manager->ensureSettings();
        $definitions = $manager->definitions();
        $settings = collect($definitions)
            ->mapWithKeys(fn ($definition): array => [
                $definition->key => $manager->setting($definition)->loadMissing('updatedBy'),
            ]);
        $runs = ScheduledTaskRun::query()
            ->with('initiator')
            ->latest('started_at')
            ->latest('id')
            ->paginate(50, ['*'], 'history_page')
            ->withQueryString();

        return view('admin.scheduler.index', compact('definitions', 'settings', 'runs'));
    }

    public function update(Request $request, string $task, ScheduledTaskManager $manager): RedirectResponse
    {
        $request->validate([
            'enabled' => ['required', 'boolean'],
            'frequency' => ['required', 'string', 'max:40'],
        ]);

        try {
            $manager->update(
                key: $task,
                enabled: $request->boolean('enabled'),
                frequency: (string) $request->input('frequency'),
                administratorId: Auth::id(),
            );
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return back()->with('success', 'Настройки задачи сохранены. Новый интервал применится не позднее следующей минуты.');
    }

    public function run(string $task, ScheduledTaskRegistry $registry, ScheduledTaskRunner $runner): RedirectResponse
    {
        try {
            $definition = $registry->find($task);
            $output = $runner->run($task, ScheduledTaskRun::TRIGGER_MANUAL, Auth::id());

            return back()->with('success', $definition->name.': '.$output);
        } catch (InvalidArgumentException) {
            abort(404);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Задачу не удалось выполнить: '.$exception->getMessage());
        }
    }
}
