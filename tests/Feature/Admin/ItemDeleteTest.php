<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Storage::fake('public');
    }

    public function test_items_list_contains_delete_button(): void
    {
        $item = $this->createItem('Предмет для списка');

        $this->get(route('admin.items'))
            ->assertOk()
            ->assertSee(route('admin.item.destroy', $item), false)
            ->assertSee('Все его экземпляры у игроков также будут удалены.')
            ->assertSee('Удалить');
    }

    public function test_item_can_be_deleted_from_admin_list(): void
    {
        Storage::disk('public')->put('items/delete-test.png', 'image');
        $item = $this->createItem('Предмет для удаления', 'items/delete-test.png');

        $this->delete(route('admin.item.destroy', $item))
            ->assertRedirect(route('admin.items'))
            ->assertSessionHas('success', 'Предмет «Предмет для удаления» удалён.');

        $this->assertDatabaseMissing('share_items', ['id' => $item->id]);
        Storage::disk('public')->assertMissing('items/delete-test.png');
    }

    public function test_shared_item_image_is_kept_until_last_item_is_deleted(): void
    {
        Storage::disk('public')->put('items/shared-test.png', 'image');
        $firstItem = $this->createItem('Первый предмет', 'items/shared-test.png');
        $secondItem = $this->createItem('Второй предмет', 'items/shared-test.png');

        $this->delete(route('admin.item.destroy', $firstItem))->assertRedirect(route('admin.items'));
        Storage::disk('public')->assertExists('items/shared-test.png');

        $this->delete(route('admin.item.destroy', $secondItem))->assertRedirect(route('admin.items'));
        Storage::disk('public')->assertMissing('items/shared-test.png');
    }

    public function test_item_used_in_game_configuration_is_not_deleted(): void
    {
        $item = $this->createItem('Награда события');
        DB::table('event_activities')->insert([
            'period' => 'daily',
            'title' => 'Тестовое событие',
            'description' => 'Проверка запрета удаления предмета.',
            'reward_share_item_id' => $item->id,
        ]);

        $this->from(route('admin.items'))
            ->delete(route('admin.item.destroy', $item))
            ->assertRedirect(route('admin.items'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('share_items', ['id' => $item->id]);
    }

    private function createItem(string $name, ?string $image = null): ShareItem
    {
        return ShareItem::query()->create([
            'name' => $name,
            'type' => ShareItemType::RESOURCE,
            'image' => $image,
        ]);
    }
}
