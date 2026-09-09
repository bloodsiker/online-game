<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('INJURIES_ENABLED', true),
    'chance_percent' => (float) env('INJURY_CHANCE_PERCENT', 30),

];
