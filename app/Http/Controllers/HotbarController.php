<?php

namespace App\Http\Controllers;

use App\Services\HotbarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotbarController extends Controller
{
    public function __construct(private HotbarService $hotbarService) {}

    public function index(): JsonResponse
    {
        $player = Auth::user()->player;

        return response()->json($this->hotbarService->getSlotsData($player));
    }

    public function set(Request $request): JsonResponse
    {
        $request->validate([
            'slot'        => ['required', 'integer', 'min:1'],
            'entity_type' => ['required', 'in:item,skill'],
            'entity_id'   => ['required', 'integer'],
        ]);

        $player = Auth::user()->player;

        $error = $this->hotbarService->setSlot(
            $player,
            $request->integer('slot'),
            $request->input('entity_type'),
            $request->integer('entity_id'),
        );

        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        return response()->json(['status' => 'success']);
    }

    public function clear(int $slot): JsonResponse
    {
        $player = Auth::user()->player;
        $this->hotbarService->clearSlot($player, $slot);

        return response()->json(['status' => 'success']);
    }
}