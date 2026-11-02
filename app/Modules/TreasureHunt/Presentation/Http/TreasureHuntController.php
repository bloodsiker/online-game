<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\TreasureHunt\Application\Services\TreasureHuntService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class TreasureHuntController extends Controller
{
    public function __construct(private readonly TreasureHuntService $service) {}

    public function check(Request $request, int $item): JsonResponse
    {
        return $this->respond(fn () => $this->service->check($request->user(), $item));
    }

    public function assemble(Request $request, int $item): JsonResponse
    {
        return $this->respond(fn () => $this->service->assemble($request->user(), $item));
    }

    public function start(Request $request, int $hunt): JsonResponse
    {
        return $this->respond(fn () => $this->service->start($request->user(), $hunt));
    }

    public function complete(Request $request, int $hunt): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'uuid']]);

        return $this->respond(fn () => $this->service->complete($request->user(), $hunt, $data['token']));
    }

    public function cancel(Request $request, int $hunt): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'uuid']]);

        return $this->respond(fn () => $this->service->cancel($request->user(), $hunt, $data['token']));
    }

    private function respond(\Closure $action): JsonResponse
    {
        try {
            return response()->json($action());
        } catch (ValidationException $error) {
            return response()->json(['ok' => false, 'message' => collect($error->errors())->flatten()->first()], 422);
        }
    }
}
