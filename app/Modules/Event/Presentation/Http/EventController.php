<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http;

use App\Modules\Event\Application\UseCases\GetActivityCards;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController
{
    public function __construct(
        private readonly GetActivityCards $getActivityCards,
    ) {}

    public function index(Request $request): View
    {
        $mode = (string) $request->query('mode', 'events');
        if (! in_array($mode, ['events', 'events_future', 'events_my', 'activity', 'rewards'], strict: true)) {
            $mode = 'events';
        }

        $period = ActivityPeriod::tryFrom((string) $request->query('group', 'daily')) ?? ActivityPeriod::DAILY;

        return view('event::index', [
            'mode' => $mode,
            'group' => $period->value,
            'activities' => $this->getActivityCards->execute($request->user()->id, $period),
        ]);
    }
}
