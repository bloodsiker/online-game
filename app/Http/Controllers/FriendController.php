<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PlayerRelationshipType;
use App\Models\Player\Player;
use App\Models\Player\PlayerRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function index(): View
    {
        $player = auth()->user()->player;

        $withTarget = ['target.user.clanMembership.clan'];
        $withPlayer = ['player.user.clanMembership.clan'];

        $friends = PlayerRelationship::where('player_id', $player->id)
            ->where('type', PlayerRelationshipType::FRIEND)
            ->where('status', 'accepted')
            ->with($withTarget)->get();

        $outgoing = PlayerRelationship::where('player_id', $player->id)
            ->where('type', PlayerRelationshipType::FRIEND)
            ->where('status', 'pending')
            ->with($withTarget)->get();

        $incoming = PlayerRelationship::where('target_id', $player->id)
            ->where('type', PlayerRelationshipType::FRIEND)
            ->where('status', 'pending')
            ->with($withPlayer)->get();

        $enemies = PlayerRelationship::where('player_id', $player->id)
            ->where('type', PlayerRelationshipType::ENEMY)
            ->with($withTarget)->get();

        $ignores = PlayerRelationship::where('player_id', $player->id)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->with($withTarget)->get();

        return view('friends.index', compact('friends', 'outgoing', 'incoming', 'enemies', 'ignores'));
    }

    public function addFriend(Request $request): RedirectResponse
    {
        $player = auth()->user()->player;
        $name   = trim((string) $request->input('name', ''));

        $target = Player::whereHas('user', fn($q) => $q->where('name', $name))->first();

        if (! $target) {
            return redirect()->back()->with('error', 'Персонаж не найден.');
        }

        if ($target->id === $player->id) {
            return redirect()->back()->with('error', 'Нельзя добавить себя.');
        }

        $exists = PlayerRelationship::where('player_id', $player->id)
            ->where('target_id', $target->id)
            ->where('type', PlayerRelationshipType::FRIEND)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Запрос уже отправлен или игрок уже в друзьях.');
        }

        PlayerRelationship::create([
            'player_id' => $player->id,
            'target_id' => $target->id,
            'type'      => PlayerRelationshipType::FRIEND,
            'status'    => 'pending',
        ]);

        return redirect()->back()->with('success', 'Запрос дружбы отправлен.');
    }

    public function acceptFriend(PlayerRelationship $relationship): RedirectResponse
    {
        $player = auth()->user()->player;

        if ($relationship->target_id !== $player->id || ! $relationship->isPending()) {
            return redirect()->back()->with('error', 'Нет такого запроса.');
        }

        $relationship->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Запрос принят.');
    }

    public function declineFriend(PlayerRelationship $relationship): RedirectResponse
    {
        $player = auth()->user()->player;

        if ($relationship->target_id !== $player->id || ! $relationship->isPending()) {
            return redirect()->back()->with('error', 'Нет такого запроса.');
        }

        $relationship->delete();

        return redirect()->back()->with('success', 'Запрос отклонён.');
    }

    public function removeFriend(PlayerRelationship $relationship): RedirectResponse
    {
        $player = auth()->user()->player;

        if ($relationship->player_id !== $player->id) {
            return redirect()->back();
        }

        $relationship->delete();

        return redirect()->back()->with('success', 'Удалён из друзей.');
    }

    public function addEnemy(Request $request): RedirectResponse
    {
        $player = auth()->user()->player;
        $name   = trim((string) $request->input('name', ''));

        $target = Player::whereHas('user', fn($q) => $q->where('name', $name))->first();

        if (! $target || $target->id === $player->id) {
            return redirect()->back()->with('error', 'Персонаж не найден.');
        }

        PlayerRelationship::firstOrCreate([
            'player_id' => $player->id,
            'target_id' => $target->id,
            'type'      => PlayerRelationshipType::ENEMY,
        ]);

        return redirect()->back()->with('success', 'Добавлен в список врагов.');
    }

    public function removeEnemy(PlayerRelationship $relationship): RedirectResponse
    {
        $player = auth()->user()->player;

        if ($relationship->player_id !== $player->id) {
            return redirect()->back();
        }

        $relationship->delete();

        return redirect()->back()->with('success', 'Удалён из врагов.');
    }

    public function addIgnore(Request $request): RedirectResponse
    {
        $player = auth()->user()->player;
        $name   = trim((string) $request->input('name', ''));

        $target = Player::whereHas('user', fn($q) => $q->where('name', $name))->first();

        if (! $target || $target->id === $player->id) {
            return redirect()->back()->with('error', 'Персонаж не найден.');
        }

        PlayerRelationship::firstOrCreate([
            'player_id' => $player->id,
            'target_id' => $target->id,
            'type'      => PlayerRelationshipType::IGNORE,
        ]);

        return redirect()->back()->with('success', 'Игрок добавлен в игнор-лист.');
    }

    public function removeIgnore(PlayerRelationship $relationship): RedirectResponse
    {
        $player = auth()->user()->player;

        if ($relationship->player_id !== $player->id) {
            return redirect()->back();
        }

        $relationship->delete();

        return redirect()->back()->with('success', 'Игрок удалён из игнор-листа.');
    }
}