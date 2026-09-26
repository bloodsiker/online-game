<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Services\SystemMessageCatalog;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use Tests\TestCase;

final class SystemMessageCatalogTest extends TestCase
{
    public function test_catalog_documents_current_global_and_personal_lifetimes(): void
    {
        $entries = collect((new SystemMessageCatalog)->entries())->keyBy('title');

        self::assertSame('30 минут', $entries->get('Глобальное системное')['lifetime']);
        self::assertSame('#cc00ff', $entries->get('Глобальное системное')['color']);
        self::assertSame('10 минут', $entries->get('Информационное')['lifetime']);
        self::assertSame('30 минут', $entries->get('Событие')['lifetime']);
        self::assertSame('#000000', $entries->get('Событие')['color']);
        self::assertSame('#009900', $entries->get('Квестовый предмет')['color']);
    }

    public function test_only_safe_visual_types_can_be_sent_from_admin_panel(): void
    {
        $types = (new SystemMessageCatalog)->sendableValues();

        self::assertContains(ChatMessageType::System->value, $types);
        self::assertContains(ChatMessageType::Information->value, $types);
        self::assertContains(ChatMessageType::Quest->value, $types);
        self::assertContains(ChatMessageType::QuestItem->value, $types);
        self::assertContains(ChatMessageType::Loot->value, $types);
        self::assertNotContains(ChatMessageType::PartyInvite->value, $types);
        self::assertNotContains(ChatMessageType::PartyNotice->value, $types);
        self::assertNotContains(ChatMessageType::WorldEvent->value, $types);
    }
}
