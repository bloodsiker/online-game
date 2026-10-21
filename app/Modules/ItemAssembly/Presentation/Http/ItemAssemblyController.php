<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\ItemAssembly\Application\Services\AssembleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ItemAssemblyController extends Controller
{
    public function store(int $id, int $assembly, AssembleItem $assembler): JsonResponse
    {
        $result = $assembler->execute(user: Auth::user(), assemblyId: $assembly, blacksmithId: $id);

        return response()->json(['ok' => $result->ok, 'success' => $result->ok, 'message' => $result->message,
            'crafted_share_item_id' => $result->itemId, 'quantity' => $result->count], $result->ok ? 200 : 422);
    }
}
