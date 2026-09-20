<?php

declare(strict_types=1);

namespace App\Modules\Chat\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chat\Application\Services\AdminChatMessageFormatter;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Application\Services\SystemMessageCatalog;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ChatAdminController extends Controller
{
    public function index(SystemMessageCatalog $catalog): View
    {
        return view('chat::admin.index', [
            'systemMessageTypes' => $catalog->entries(),
            'sendableMessageTypes' => $catalog->sendableEntries(),
        ]);
    }

    public function send(
        Request $request,
        ChatService $chatService,
        SystemMessageCatalog $catalog,
        AdminChatMessageFormatter $formatter,
    ): RedirectResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'type' => ['required', 'string', Rule::in($catalog->sendableValues())],
        ]);

        $message = $formatter->clean($validated['message']);
        if ($formatter->textLength($message) < 2) {
            throw ValidationException::withMessages([
                'message' => 'Введите не менее двух символов текста сообщения.',
            ]);
        }

        $chatService->sendSystem(
            $message,
            type: ChatMessageType::from($validated['type']),
        );

        return redirect()
            ->route('admin.chat.index')
            ->with('success', 'Системное сообщение отправлено всем игрокам.');
    }
}
