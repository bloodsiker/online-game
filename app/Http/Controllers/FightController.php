<?php

namespace App\Http\Controllers;

use App\DTO\FightDTO;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Services\Battle\BattleOrchestrator;
use App\Services\BattleService;
use App\Services\Combat\FightOrchestrator;
use Illuminate\Support\Facades\Auth;

class FightController extends Controller
{
    public function __construct(
        readonly protected BattleOrchestrator $battleOrchestrator,
        readonly protected BattleService $battleService,
        readonly protected FightOrchestrator $fightOrchestrator,
    ) {
    }

    public function index($id)
    {
        $user = Auth::user();
        $player = $user->player;
        $battle = Battle::find($id);

        if ($user->currentLocation !== $battle->location_id) {

        }

        $randomAttackedMonster = BattleDetail::with(['locationMonster'])
            ->whereNotNull('location_monster_id')
            ->where(['battle_id' => $battle->id])
            ->where('status', 1)
            ->inRandomOrder()
            ->first();

        return view('fight.index', compact('battle', 'randomAttackedMonster', 'player'));
    }

    public function attack(int $id, int $monsterId, int $action)
    {
        $fightDTO = $this->fightOrchestrator->attack($id, $monsterId, $action);
        $battle = $fightDTO->getBattle();
        $round = $fightDTO->getBattleRound();
        $player = $fightDTO->getPlayer();

        if ($fightDTO->isPlayerDead()) {
            $attackedMonster = $fightDTO->getAttackedMonster();

            return view('fight.death', compact('battle', 'player',  'round', 'attackedMonster', 'fightDTO'));
        }

        $randomAttackedMonster = BattleDetail::with(['locationMonster'])
            ->whereNotNull('location_monster_id')
            ->where(['battle_id' => $battle->id, 'status' => 1])
            ->inRandomOrder()
            ->first();

        return view('fight.attack', compact('battle', 'round',  'randomAttackedMonster', 'player', 'fightDTO'));
    }

    public function attackMonster($id)
    {
        $user = Auth::user();
        $player = $user->player;

//        $battle = $this->battleOrchestrator->handlePlayerAttack($user->currentLocation, $id);
        $battle = $this->battleService->attackMonster($user->currentLocation, $id);

        $randomAttackedMonster = BattleDetail::with(['locationMonster'])
            ->where(['location_monster_id' => $id])
            ->where('status', 1)
            ->first();

        return view('fight.index', compact('battle', 'randomAttackedMonster', 'player'));
    }

    public function runAway($id)
    {
        $user = Auth::user();
        $player = $user->player;
        $prevLocation = $user->prev_location_id;

        $chanceRun = mt_rand(0, 100);

        $battle = Battle::find($id);
        if ($battle->status === 1) {
            if ($chanceRun > 50) {
                $usersInBattle = BattleDetail::where('user_id', '!=', $user->id)->whereNotNull('user_id')->count();
                if ($usersInBattle > 0) {
                    $battleRound = new BattleRound();
                    $battleRound->battle_id = $battle->id;
                    $battleRound->round_number = ++$battle->rounds;
                    $battleRound->user_id = $user->id;
                    $battleRound->action = "<p>Вы бежали на восток...</p>";
                    $battleRound->save();

                    BattleDetail::where('user_id', $user->id)->delete();
                } else {
                    $battle->status = 0;
                    $battle->save();

                    $battleRound = new BattleRound();
                    $battleRound->battle_id = $battle->id;
                    $battleRound->round_number = ++$battle->rounds;
                    $battleRound->user_id = $user->id;
                    $battleRound->action = "<p>Вы бежали на восток...</p>";
                    $battleRound->save();
                }
            } else {
                $randomAttackedMonster = BattleDetail::with(['locationMonster'])
                    ->whereNotNull('location_monster_id')
                    ->where(['battle_id' => $battle->id])
                    ->where('status', 1)
                    ->inRandomOrder()
                    ->first();

                $round = new BattleRound();
                $round->battle_id = $battle->id;
                $round->round_number = $battle->rounds;
                $round->user_id = $user->id;
                $round->location_monster_id = $randomAttackedMonster->location_monster_id;
                $round->action = "<p>Вы попытались бежать... не получилось.</p>";
                $round->save();

                $battle->rounds += 1;
                $battle->save();

                $fightDTO = (new FightDTO())
                    ->setBattle($battle)
                    ->setBattleRound($round)
                    ->setPlayer($player);

                return view('fight.attack', compact('battle', 'round',  'randomAttackedMonster', 'player', 'fightDTO'));
            }
        }

        $user->prev_location_id = $user->location_id;
        $user->location_id = $prevLocation;
        $user->save();

        return view('fight.run_away');
    }
}
