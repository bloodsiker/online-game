<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Final review, IMPORTANT 7: ни DatabaseSeeder, ни artisan-команда GenerateSeed
 * не звали AttackSkillSeeder и MagicBookStarterSeeder — свежее окружение после
 * `php artisan db:seed` получало ноль заклинаний и ноль книг.
 *
 * Порядок обязателен: MagicBookStarterSeeder::bookifyExistingAttackSpells()
 * оборачивает в книги слаги, которые создаёт AttackSkillSeeder.
 */
class MagicSeedersAreRegisteredTest extends TestCase
{
    /** @return iterable<string, array{0: string}> */
    public static function entryPoints(): iterable
    {
        yield 'DatabaseSeeder' => ['database/seeders/DatabaseSeeder.php'];
        yield 'GenerateSeed command' => ['app/Console/Commands/GenerateSeed.php'];
    }

    #[DataProvider('entryPoints')]
    public function test_magic_content_seeders_are_called_in_dependency_order(string $relativePath): void
    {
        $source = (string) file_get_contents(base_path($relativePath));

        // Ищем именно место вызова (`Seeder::class` или `'Seeder'` в --class),
        // а не любое упоминание имени — комментарии не считаются.
        $attackPosition = $this->callPosition($source, 'AttackSkillSeeder');
        $bookPosition = $this->callPosition($source, 'MagicBookStarterSeeder');

        $this->assertNotNull($attackPosition, $relativePath.' обязан звать AttackSkillSeeder');
        $this->assertNotNull($bookPosition, $relativePath.' обязан звать MagicBookStarterSeeder');
        $this->assertLessThan(
            $bookPosition,
            $attackPosition,
            'AttackSkillSeeder обязан идти раньше MagicBookStarterSeeder — книги оборачивают его заклинания',
        );
    }

    private function callPosition(string $source, string $seeder): ?int
    {
        $pattern = sprintf('/%1$s::class|\'%1$s\'/', preg_quote($seeder, '/'));

        return preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 1
            ? (int) $matches[0][1]
            : null;
    }
}
