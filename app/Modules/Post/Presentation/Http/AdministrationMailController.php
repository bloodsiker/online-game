<?php

declare(strict_types=1);

namespace App\Modules\Post\Presentation\Http;

use App\Modules\Post\Application\Services\AdministrationMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class AdministrationMailController
{
    public function __construct(private AdministrationMailService $mail) {}

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:64'],
            'text' => ['required', 'string', 'max:10000'],
            'thread_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $thread = $this->mail->send(
            player: $request->user(), subject: $data['subject'], text: $data['text'],
            threadId: isset($data['thread_id']) ? (int) $data['thread_id'] : null,
        );

        return redirect()->route('post', ['mode' => 'administration', 'reply_to' => $thread->id])
            ->with('post_success', __('post.administration_sent'));
    }
}
