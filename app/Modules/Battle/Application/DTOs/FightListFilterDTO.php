<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\DTOs;

use Illuminate\Http\Request;

final readonly class FightListFilterDTO
{
    public function __construct(
        public string $nick = '',
        public string $monsterName = '',
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $form = (array) $request->query('form', []);

        return new self(
            nick: trim((string) ($form['nick'] ?? '')),
            monsterName: trim((string) ($form['title'] ?? '')),
            dateFrom: self::composeDate($form['start_dd'] ?? null, $form['start_mm'] ?? null, $form['start_yy'] ?? null),
            dateTo: self::composeDate($form['end_dd'] ?? null, $form['end_mm'] ?? null, $form['end_yy'] ?? null),
        );
    }

    private static function composeDate(mixed $day, mixed $month, mixed $year): ?string
    {
        if (! $day || ! $month || ! $year) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
    }
}
