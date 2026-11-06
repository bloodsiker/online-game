<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Console;

use App\Modules\News\Application\UseCases\SendLaunchAnnouncements;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

final class AnnounceGameLaunch extends Command implements Isolatable
{
    protected $signature = 'game:announce-launch {--send : Отправить письма об открытии игры}';

    protected $description = 'Показать число ожидающих подписчиков или отправить уведомления об открытии';

    protected $isolated = true;

    public function handle(SendLaunchAnnouncements $announcements): int
    {
        $this->info('Ожидают уведомления: '.$announcements->pendingCount());
        if (! $this->option('send')) {
            $this->info('Письма не отправлены. Для рассылки после открытия используйте --send.');

            return self::SUCCESS;
        }
        if (config('game.prelaunch')) {
            $this->error('Сначала откройте игру: GAME_PRELAUNCH=false.');

            return self::FAILURE;
        }
        $result = $announcements->execute();
        $this->info('Отправлено: '.$result['sent'].'. Ошибок: '.$result['failed'].'.');

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
