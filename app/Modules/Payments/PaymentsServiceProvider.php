<?php

declare(strict_types=1);

namespace App\Modules\Payments;

use App\Modules\Payments\Presentation\Console\SyncNbuRate;
use App\Modules\Payments\Presentation\Http\HeleketWebhookController;
use App\Modules\Payments\Presentation\Http\MonobankWebhookController;
use App\Modules\Payments\Presentation\Http\PaymentOrderController;
use App\Modules\Payments\Presentation\Http\TrybitWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class PaymentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'payments');
        if ($this->app->runningInConsole()) {
            $this->commands([SyncNbuRate::class]);
        }
        Route::middleware(['web', 'auth'])->group(function (): void {
            Route::post('/payments/orders', [PaymentOrderController::class, 'store'])->middleware('throttle:10,1')->name('payments.orders.store');
            Route::get('/payments/orders/{publicId}', [PaymentOrderController::class, 'show'])->name('payments.orders.show');
            Route::get('/payments/orders/{publicId}/status', [PaymentOrderController::class, 'status'])->middleware('throttle:60,1')->name('payments.orders.status');
            Route::post('/payments/orders/{publicId}/pay', [PaymentOrderController::class, 'pay'])->middleware('throttle:10,1')->name('payments.orders.pay');
            Route::post('/payments/orders/{publicId}/cancel', [PaymentOrderController::class, 'cancel'])->name('payments.orders.cancel');
        });
        // Вебхук Heleket: без сессии и CSRF, доверие — по подписи (и IP отправителя).
        Route::post('/payments/heleket/webhook', HeleketWebhookController::class)
            ->middleware('throttle:120,1')->name('payments.heleket.webhook');
        // Вебхук plata by mono: доверие — по подписи X-Sign (ECDSA).
        Route::post('/payments/monobank/webhook', MonobankWebhookController::class)
            ->middleware('throttle:120,1')->name('payments.monobank.webhook');
        // POSTBACK Trybit: доверие — по JWT (SECRET KEY) и перепроверке счёта через API.
        Route::post('/payments/trybit/webhook', TrybitWebhookController::class)
            ->middleware('throttle:120,1')->name('payments.trybit.webhook');
    }
}
