<?php

declare(strict_types=1);

namespace App\Modules\Post\Presentation\Http\Admin;

use App\Modules\Post\Application\UseCases\SendSystemLetter;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PostAdminController
{
    public function __construct(
        private readonly SendSystemLetter $sendSystemLetter,
    ) {}

    public function index(): View
    {
        $letters = PostLetter::query()
            ->with(['recipient', 'shareItem'])
            ->whereNull('sender_user_id')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('post::admin.send', ['letters' => $letters]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to_all' => ['nullable', 'boolean'],
            'nick' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:64'],
            'text' => ['required', 'string'],
            'money' => ['nullable', 'integer', 'min:0'],
            'share_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
            'item_amount' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $this->sendSystemLetter->execute(
            nick: (string) ($data['nick'] ?? ''),
            subject: $data['subject'],
            text: $data['text'],
            money: (int) ($data['money'] ?? 0),
            shareItemId: isset($data['share_item_id']) ? (int) $data['share_item_id'] : null,
            itemAmount: (int) ($data['item_amount'] ?? 1),
            toAll: $request->boolean('to_all'),
        );

        return $result->ok
            ? redirect()->route('admin.post.send')->with('success', $result->message)
            : redirect()->route('admin.post.send')->withErrors($result->message)->withInput();
    }
}
