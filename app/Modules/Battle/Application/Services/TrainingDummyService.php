<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Player\Application\Services\FatigueService;
use App\Modules\Player\Application\Services\SleepEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\Cache;

/**
 * Статистика тренировки на чучеле (по игроку и бою) и усталость за каждый удар.
 * Статистика живёт в кэше: бой закончился — тренировка начинается заново.
 */
final class TrainingDummyService
{
    private const SOURCE = 'training_dummy';

    private const TTL_SECONDS = 21600;

    public function __construct(private readonly FatigueService $fatigue) {}

    public function recordHit(Battle $battle, Player $player, int $damage, bool $critical): void
    {
        $stats = $this->stats($battle, $player);
        $stats['hits']++;
        $stats['total'] += $damage;
        $stats['max'] = max($stats['max'], $damage);
        $stats['min'] = $stats['min'] === null ? $damage : min($stats['min'], $damage);
        if ($critical) {
            $stats['crits']++;
        }
        $this->save($battle, $player, $stats);
        $this->fallAsleepIfTired($player);
    }

    public function recordMiss(Battle $battle, Player $player): void
    {
        $stats = $this->stats($battle, $player);
        $stats['misses']++;
        $this->save($battle, $player, $stats);
        $this->fallAsleepIfTired($player);
    }

    private function fallAsleepIfTired(Player $player): void
    {
        if ($this->fatigue->add($player, self::SOURCE) && $player->sleep_until !== null) {
            app(SleepEffectService::class)->apply($player, $player->sleep_until);
        }
    }

    /** Таблица статистики под логом боя (разовая, в историю раундов не пишется). */
    public function summaryTable(Battle $battle, Player $player): string
    {
        $stats = $this->stats($battle, $player);
        $attempts = $stats['hits'] + $stats['misses'];
        if ($attempts === 0) {
            return '';
        }

        $average = $stats['hits'] > 0 ? $stats['total'] / $stats['hits'] : 0.0;
        $critPercent = $stats['hits'] > 0 ? $stats['crits'] / $stats['hits'] * 100 : 0.0;

        $rows = [
            [__('Ударов'), (string) $attempts],
            [__('Сумма урона'), (string) $stats['total']],
            [__('Средний урон'), $this->number($average)],
            [__('Мин / макс'), ($stats['min'] ?? 0).' / '.$stats['max']],
            [__('Криты'), $this->number($critPercent).'%'],
            [__('Промахи'), (string) $stats['misses']],
        ];

        $cells = '';
        foreach ($rows as [$label, $value]) {
            $cells .= '<tr><td style="padding:3px 4px;border-bottom:1px solid #EDD5C3;color:#5a1f00;">'.e($label).'</td>'
                .'<td style="padding:3px 4px;border-bottom:1px solid #EDD5C3;text-align:right;font-weight:bold;color:#5a1f00;">'.e($value).'</td></tr>';
        }

        $fatigue = $this->fatigue->current($player);
        $max = $this->fatigue->max();
        $asleep = $this->fatigue->isAsleep($player);
        $percent = $max > 0 ? min(100, (int) round($fatigue / $max * 100)) : 0;
        $fill = $asleep || $fatigue >= $this->fatigue->warnAt()
            ? 'linear-gradient(to bottom,#ffc36a,#d97806 55%,#9a5200)'
            : 'linear-gradient(to bottom,#6ac0ff,#1a6fc1 55%,#0f4a8a)';
        $note = match (true) {
            $asleep => '<div style="color:#BA0000;font-weight:bold;margin-top:5px;">💤 '.e(__('Вы уснули от переутомления и не можете тренироваться, пока не проснётесь.')).'</div>',
            $fatigue >= $this->fatigue->warnAt() => '<div style="color:#BA0000;margin-top:5px;">'.e(__('Скоро уснёте.')).'</div>',
            default => '',
        };

        return '<table border="0" cellspacing="0" cellpadding="0" class="fight-window" style="margin:8px 0;width:260px;max-width:100%;"><tbody>'
            .'<tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>'
            .'<td class="tbl-shp-sml tt" valign="top" align="center"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">'
            .'<td width="27" class="tbl-usi-hdr lc"><b></b></td><td align="center" class="tbl-usi-hdr mbg">'.e(__('Статистика тренировки')).'</td><td width="27" class="tbl-usi-hdr rc"><b></b></td>'
            .'</tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr>'
            .'<tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:4px 6px 6px;font-size:12px;">'
            .'<table style="border-collapse:collapse;width:100%;">'.$cells.'</table>'
            .'<div style="margin-top:6px;color:#5a1f00;">'.e(__('Усталость')).': <b>'.$fatigue.'/'.$max.'</b></div>'
            .'<div style="height:7px;margin-top:2px;padding:1px;background:#6e534c;border:1px solid #6f4a24;box-shadow:inset 0 1px 2px rgba(36,18,4,.6);box-sizing:border-box;">'
            .'<div style="height:100%;width:'.$percent.'%;background:'.$fill.';"></div></div>'
            .$note.'</td><td class="tbl-shp-sides rs">&nbsp;</td></tr>'
            .'<tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>'
            .'<td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr>'
            .'</tbody></table>';
    }

    /** @return array{hits:int,misses:int,crits:int,total:int,max:int,min:?int} */
    private function stats(Battle $battle, Player $player): array
    {
        return Cache::get($this->key($battle, $player)) ?? ['hits' => 0, 'misses' => 0, 'crits' => 0, 'total' => 0, 'max' => 0, 'min' => null];
    }

    /** @param  array{hits:int,misses:int,crits:int,total:int,max:int,min:?int}  $stats */
    private function save(Battle $battle, Player $player, array $stats): void
    {
        Cache::put($this->key($battle, $player), $stats, self::TTL_SECONDS);
    }

    private function key(Battle $battle, Player $player): string
    {
        return 'training-dummy:'.$battle->id.':'.$player->id;
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }
}
