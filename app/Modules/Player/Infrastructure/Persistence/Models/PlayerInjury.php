<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $player_id
 * @property int|null $injury_type_id
 * @property InjuryBodyPart $body_part
 * @property InjurySeverity $severity
 * @property Carbon $applied_at
 * @property Carbon $expires_at
 * @property-read InjuryType|null $injuryType
 */
class PlayerInjury extends Model
{
    protected $fillable = [
        'player_id',
        'injury_type_id',
        'body_part',
        'severity',
        'applied_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'body_part' => InjuryBodyPart::class,
            'severity' => InjurySeverity::class,
            'applied_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function injuryType(): BelongsTo
    {
        return $this->belongsTo(InjuryType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function penaltyPercent(): float
    {
        return (float) config(
            'injuries.levels.'.$this->severity->value.'.stat_penalty_percent',
            $this->severity->value * 5,
        );
    }

    /** @return list<array{stat: string, value: float|int, is_percent: bool}> */
    public function statModifiers(): array
    {
        $configured = $this->injuryType?->stat_modifiers;
        if (is_array($configured)) {
            return array_values(array_filter(
                $configured,
                static fn (mixed $modifier): bool => is_array($modifier) && is_string($modifier['stat'] ?? null),
            ));
        }

        return array_map(fn (string $stat): array => [
            'stat' => $stat,
            'value' => -$this->penaltyPercent(),
            'is_percent' => true,
        ], $this->body_part->affectedStats());
    }

    public function displayName(): string
    {
        return $this->injuryType?->name
            ?? $this->severity->label().' травма: '.mb_strtolower($this->body_part->label());
    }

    public function imageUrl(): ?string
    {
        return $this->injuryType?->image;
    }

    public function tooltipId(): string
    {
        return 'injury_'.$this->getKey();
    }

    public function remainingLabel(): string
    {
        $seconds = max(0, (int) ceil(now()->diffInSeconds($this->expires_at, false)));
        $minutes = intdiv($seconds, 60);

        return sprintf('%02d:%02d', $minutes, $seconds % 60);
    }

    public function tooltip(): string
    {
        $modifiers = collect($this->statModifiers())
            ->map(static function (array $modifier): string {
                $value = (float) ($modifier['value'] ?? 0);
                $formatted = rtrim(rtrim(number_format(abs($value), 2, '.', ''), '0'), '.');
                $unit = ! empty($modifier['is_percent']) ? '%' : '';
                $stat = PlayerStatKey::tryFrom((string) ($modifier['stat'] ?? ''))?->label()
                    ?? (string) ($modifier['stat'] ?? 'Характеристика');

                return sprintf('%s: %s%s%s', $stat, $value >= 0 ? '+' : '−', $formatted, $unit);
            })
            ->implode(', ');

        return sprintf(
            '%s%s. Осталось %s',
            $this->displayName(),
            $modifiers !== '' ? '. Модификаторы: '.$modifiers : '',
            $this->remainingLabel(),
        );
    }
}
