<?php

declare(strict_types=1);

namespace App\Modules\Post\Presentation\Http\Admin;

use App\Modules\Post\Application\Services\AdministrationMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class AdministrationMailController
{
    public function __construct(private AdministrationMailService $mail) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $threads = $this->mail->threads()->with('sender')
            ->when(in_array($status, ['pending', 'answered'], true), fn ($query) => $query->where('administration_status', $status))
            ->orderByDesc('administration_last_message_at')->orderByDesc('id')->paginate(50)->withQueryString();

        return view('post::admin.administration-index', compact('threads', 'status'));
    }

    public function show(int $id): View
    {
        $thread = $this->mail->thread($id);
        $messages = $this->mail->messages($thread);

        return view('post::admin.administration-thread', compact('thread', 'messages'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['text' => ['required', 'string', 'max:10000']]);
        $this->mail->reply(admin: $request->user(), threadId: $id, text: $data['text']);

        return redirect()->route('admin.post.administration.show', $id)->with('success', 'Ответ отправлен в игровую почту.');
    }
}
