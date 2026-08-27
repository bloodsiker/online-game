<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Application\UseCases\GetClanSkillsPage;
use App\Modules\Clan\Application\UseCases\LearnClanSkill;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ClanSkillController extends Controller
{
    public function __construct(
        protected readonly GetClanSkillsPage $getClanSkillsPage,
        protected readonly LearnClanSkill $learnClanSkill,
    ) {}

    public function index(): mixed
    {
        try {
            $page = $this->getClanSkillsPage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::skills', [
            'clan' => $page->clan,
            'membership' => $page->membership,
            'definitions' => $page->definitions,
            'learnedMap' => $page->learnedMap,
            'canLearn' => $page->canLearn,
            'backpackShareItemCounts' => $page->backpackShareItemCounts,
            'player' => $page->player,
            'playerDecorator' => $page->playerDecorator,
        ]);
    }

    public function learn(int $id): mixed
    {
        try {
            $error = $this->learnClanSkill->execute(Auth::user(), $id);

            if ($error) {
                session()->flash('message', $error);
            } else {
                session()->flash('success', 'Навык успешно улучшен!');
            }
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.skills');
    }
}
