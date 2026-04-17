<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\DTOs;

final readonly class ChatMessageDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public string $channel,
        public string $sender_name,
        public ?string $target_name,
        public int $sender_id,
        public string $content,
        public string $time,
        public bool $is_own,
        public ?string $reply_to,
    ) {}
}
