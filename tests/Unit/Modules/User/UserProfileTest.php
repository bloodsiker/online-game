<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\User;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Infrastructure\Persistence\Models\UserProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UserProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Тест разрешён только в SQLite :memory:.');
        }
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('password')->nullable();
        });
        (require database_path('migrations/2026_10_10_000000_create_user_profiles_table.php'))->up();
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
    }

    private function save(array $data)
    {
        return $this->actingAs(User::query()->without('player')->findOrFail(1))
            ->withSession(['_token' => 'profile-token'])
            ->post(route('settings.profile.update'), ['_token' => 'profile-token', ...$data]);
    }

    public function test_saves_all_fields_only_for_authenticated_user_and_renders_escaped_values(): void
    {
        $this->save([
            'user_id' => 2, 'real_name' => 'Дмитрий', 'residence' => 'Киев',
            'motto' => 'Вперёд', 'about' => '<script>alert(1)</script>',
            'birth_day' => 29, 'birth_month' => 2, 'birth_year' => 2000,
        ])->assertRedirect(route('settings.profile'))->assertSessionHas('message', 'Анкета сохранена.');
        $profile = UserProfile::query()->sole();
        $this->assertSame(1, $profile->user_id);
        $this->assertSame('Дмитрий', $profile->real_name);
        $this->assertSame('Киев', $profile->residence);
        $this->assertSame('Вперёд', $profile->motto);
        $this->assertSame('2000-02-29', $profile->birth_date->toDateString());
        $this->get(route('settings.profile'))->assertOk()
            ->assertSee('window.parent.showErrorIframe?.(', false)
            ->assertDontSee('role="status"', false)
            ->assertSee('value="Дмитрий"', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_existing_profile_can_be_updated_and_cleared(): void
    {
        $this->save(['real_name' => 'Имя', 'birth_day' => 1, 'birth_month' => 1, 'birth_year' => 2000]);
        $this->save(['real_name' => '', 'birth_day' => '', 'birth_month' => '', 'birth_year' => ''])->assertSessionHasNoErrors();
        $profile = UserProfile::query()->sole();
        $this->assertNull($profile->real_name);
        $this->assertNull($profile->birth_date);
    }

    public function test_invalid_partial_future_dates_and_overlong_fields_are_rejected(): void
    {
        foreach ([
            ['birth_day' => 31, 'birth_month' => 2, 'birth_year' => 2000],
            ['birth_day' => 1],
            ['birth_day' => 31, 'birth_month' => 12, 'birth_year' => now()->year + 1],
            ['real_name' => str_repeat('a', 33)],
            ['about' => str_repeat('a', 5001)],
        ] as $data) {
            $this->save($data)->assertSessionHasErrors();
        }
        $this->assertSame(0, UserProfile::query()->count());
    }

    public function test_guest_and_requests_without_csrf_cannot_save(): void
    {
        $this->withSession(['_token' => 'profile-token'])->postJson(route('settings.profile.update'), ['_token' => 'profile-token'])->assertUnauthorized();
        $this->actingAs(User::query()->without('player')->findOrFail(1))
            ->postJson(route('settings.profile.update'), ['real_name' => 'Имя'])->assertStatus(419);
        $this->assertSame(0, UserProfile::query()->count());
    }

    public function test_public_profile_displays_all_saved_fields_and_escapes_html(): void
    {
        $this->save([
            'real_name' => 'Дмитрий', 'residence' => 'Киев', 'motto' => 'Вперёд',
            'about' => "Первая строка\n<script>alert(1)</script>",
            'birth_day' => 29, 'birth_month' => 2, 'birth_year' => 2000,
        ]);
        $user = User::query()->without('player')->with('profile')->findOrFail(1);
        $html = view('user::partials.personal-info', ['profile' => $user->profile, 'nickname' => 'Воин', 'age' => '1 год'])->render();
        foreach (['Дмитрий', 'Киев', 'Вперёд', '29.02.2000', 'Воин', 'О себе:', '&lt;script&gt;alert(1)&lt;/script&gt;', 'white-space:pre-wrap'] as $value) {
            $this->assertStringContainsString($value, $html);
        }
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $source = file_get_contents(app_path('Modules/User/Presentation/Views/info.blade.php'));
        $this->assertStringContainsString("@include('user::partials.personal-info'", $source);
    }

    public function test_public_profile_hides_empty_fields_and_handles_missing_profile(): void
    {
        foreach ([null, new UserProfile(['motto' => 'Девиз'])] as $profile) {
            $html = view('user::partials.personal-info', ['profile' => $profile, 'nickname' => 'Воин', 'age' => '1 год'])->render();
            foreach (['Дата рождения:', 'Место проживания:', 'О себе:'] as $label) {
                $this->assertStringNotContainsString($label, $html);
            }
            $this->assertStringContainsString('Никнейм:', $html);
        }
    }
}
