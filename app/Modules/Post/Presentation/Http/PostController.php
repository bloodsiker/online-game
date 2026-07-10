<?php

declare(strict_types=1);

namespace App\Modules\Post\Presentation\Http;

use App\Modules\Post\Application\UseCases\BulkLetters;
use App\Modules\Post\Application\UseCases\DeleteLetter;
use App\Modules\Post\Application\UseCases\GetMailbox;
use App\Modules\Post\Application\UseCases\ReadLetter;
use App\Modules\Post\Application\UseCases\SendLetter;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PostController
{
    public function __construct(
        private readonly GetMailbox $mailbox,
        private readonly SendLetter $sendLetter,
        private readonly ReadLetter $readLetter,
        private readonly DeleteLetter $deleteLetter,
        private readonly BulkLetters $bulkLetters,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(Request $request): View
    {
        $mode = (string) $request->query('mode', 'inbox');
        if (! in_array($mode, ['inbox', 'outpost', 'outbox'], strict: true)) {
            $mode = 'inbox';
        }

        $user = $request->user();

        return view('post::index', [
            'mode' => $mode,
            'letters' => match ($mode) {
                'inbox' => $this->mailbox->inbox($user),
                'outpost' => $this->mailbox->sent($user),
                default => collect(),
            },
            'inboxCount' => $this->mailbox->inboxCount($user),
            'capacity' => GetMailbox::CAPACITY,
            'tax' => SendLetter::TAX,
            'letter' => null,
            'itemTooltipScript' => '',
        ]);
    }

    public function letter(Request $request, int $id): View|RedirectResponse
    {
        $user = $request->user();
        $letter = $this->readLetter->execute($user, $id);

        if ($letter === null) {
            return redirect()->route('post');
        }

        // Письмо показывается в правом окне, список остаётся слева — как на проде
        $mode = $letter->recipient_user_id === $user->id ? 'inbox' : 'outpost';

        if ($letter->shareItem) {
            $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy([$letter->shareItem]));
        }

        return view('post::index', [
            'mode' => $mode,
            'letters' => $mode === 'inbox' ? $this->mailbox->inbox($user) : $this->mailbox->sent($user),
            'inboxCount' => $this->mailbox->inboxCount($user),
            'capacity' => GetMailbox::CAPACITY,
            'tax' => SendLetter::TAX,
            'letter' => $letter,
            'itemTooltipScript' => $letter->shareItem ? $this->tooltipCollector->renderScript() : '',
        ]);
    }

    public function bulk(Request $request): RedirectResponse
    {
        $action = (string) $request->input('action', '');
        $ids = (array) $request->input('ids', []);
        $mode = $request->input('from') === 'outpost' ? 'outpost' : 'inbox';

        $affected = $this->bulkLetters->execute($request->user(), $ids, $action);

        return redirect()->route('post', ['mode' => $mode])->with(
            $affected > 0 ? 'post_success' : 'post_error',
            $affected > 0
                ? match ($action) {
                    'claim' => 'Ценности забраны.',
                    'delete' => 'Письма удалены.',
                    default => 'Ценности забраны, письма удалены.',
                }
            : 'Выберите письма.',
        );
    }

    public function send(Request $request): RedirectResponse
    {
        $result = $this->sendLetter->execute(
            sender: $request->user(),
            nick: (string) $request->input('nick', ''),
            subject: (string) $request->input('subject', ''),
            text: (string) $request->input('text', ''),
            money: (int) $request->input('money', 0),
        );

        if (! $result->ok) {
            return redirect()->route('post', ['mode' => 'outbox'])
                ->with('post_error', $result->message)
                ->withInput();
        }

        return redirect()->route('post', ['mode' => 'outpost'])->with('post_success', $result->message);
    }

    public function claim(Request $request, int $id): RedirectResponse
    {
        $affected = $this->bulkLetters->execute($request->user(), [$id], 'claim');

        return redirect()->route('post.letter', $id)->with(
            $affected > 0 ? 'post_success' : 'post_error',
            $affected > 0 ? 'Вложения получены.' : 'Забирать нечего.',
        );
    }

    public function delete(Request $request, int $id): RedirectResponse
    {
        $this->deleteLetter->execute($request->user(), $id);

        $mode = (string) $request->query('from', 'inbox');

        return redirect()->route('post', ['mode' => $mode === 'outpost' ? 'outpost' : 'inbox'])
            ->with('post_success', 'Письмо удалено.');
    }
}
