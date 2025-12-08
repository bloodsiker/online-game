<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function login($id)
    {
        $user = User::find($id);
        Auth::login($user, true);

        return redirect()->route('game');
//        return view('homepage');
    }

    public function gebug()
    {
        $diff = time() - Carbon::parse('2024-09-20 15:53')->timestamp;
        dd($diff / 60);
//        $user = User::find(1);
//        $player = $user->player;
//        $player = new EquipmentDecorator($player);
//        $player = new BuffDecorator($player);
//
//        echo "Min Left dmg: " . $player->getLeftHandMinDmg() . PHP_EOL . "\n";
//        echo "Max Left dmg: " . $player->getLeftHandMaxDmg() . PHP_EOL . PHP_EOL;
//        echo "Min Right dmg: " . $player->getRightHandMinDmg() . PHP_EOL . "\n";
//        echo "Max Right dmg: " . $player->getRightHandMaxDmg() . PHP_EOL . PHP_EOL;
//        echo "Сила: " . $player->getStrength() . PHP_EOL . PHP_EOL;
//        echo "Ловкость: " . $player->getAgility(). PHP_EOL;
//        echo "Интеллект: " . $player->getIntelligence();

//        dd($finalDamage = 120 * (500 / (500 + 900)));

        $attaker = new \StdClass();
        $attaker->min_dmg = 5;
        $attaker->max_dmg = 20;
        $attaker->crit = 100;
        $attaker->armor = 15;

        $defender = new \StdClass();
        $defender->min_dmg = 3;
        $defender->max_dmg = 16;
        $defender->crit = 200;
        $defender->armor = 25;

        $dodge = $this->calculateDodge(200, 500);
        $crit = $this->calculateCrit(1000, 1500);
        $dmg = $this->calculateDamage($attaker, $defender);
        dd($dodge, $crit, $dmg);
    }

    /**
     * Расчет уворота на основе ловкости атакующего и защищающегося
     */
    protected function calculateDodge($attackerAgility, $defenderAgility)
    {
        return $dodgeChance = max(0, min(100, 50 + ($defenderAgility - $attackerAgility) * 0.05));  // Линейная зависимость
        return mt_rand(0, 100) < $dodgeChance;
    }

    /**
     * Расчет шанса крита на основе параметров атакующего и устойчивости к критам защитника
     */
    protected function calculateCrit($attackerCrit, $defenderCritResistance)
    {
        $critChance = max(0, min(100, 50 + ($attackerCrit - $defenderCritResistance) * 0.05));
//        return $critChance = max(0, min(100, 50 + ($attackerCrit - $defenderCritResistance) * 0.05));
        return mt_rand(0, 100) < $critChance;
    }

    /**
     * Расчет урона с учетом брони и шанса крита
     */
    protected function calculateDamage($attacker, $defender)
    {
        $baseDamage = mt_rand($attacker->min_dmg, $attacker->max_dmg);

        // Рассчитываем шанс крита на основе параметров
        if ($this->calculateCrit($attacker->crit, $defender->crit)) {
            $baseDamage *= 2;  // Критический удар
        }

        // Учитываем броню защитника
        $finalDamage = $baseDamage * (500 / (500 + $defender->armor)); // Чем выше броня, тем меньше урон

        return max(0, round($finalDamage));
    }

    protected function calculateExperience()
    {
        $playerLvl = 12;
        $monsterLvl = 1;
        $damage = 10;
        $hpMonster = 10;
        $baseExperience = 100;
        $takeExp = $damage * $baseExperience / $hpMonster;
        $levelDifference = $playerLvl - $monsterLvl;
        $experienceMultiplier = max(0.01, 1 - 0.05 * $levelDifference); // Уменьшение опыта на 5% за каждый уровень

        // Опыт рассчитывается как базовый опыт, умноженный на урон и корректировка по уровню
        dump(max(1, $takeExp * $experienceMultiplier));
    }

    public function map()
    {
        return view('maps.city.map');
    }

    public function map2()
    {
        return view('maps.subcity.map');
    }

    public function map3()
    {
        return view('maps.catacomb_sacrifice.map');
    }

    public function clear()
    {
        Artisan::call('cache:clear');
    }
}
