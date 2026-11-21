{{-- Публичный прайс (/pricing): что продаётся, цены, как покупается премиум, оплата и возврат. --}}
@php
    $p = $pricing;
    $usd = fn (int $minor): string => number_format($minor / 100, 2, '.', ' ');
@endphp
<style>
    .pricing { color: #3b2717; font: 11px/1.65 Tahoma, Arial, sans-serif; }
    .pricing h1 { margin: 4px 0 10px; color: #8b1a1a; font: bold 16px/22px Tahoma, Arial, sans-serif; text-align: center; }
    .pricing h2 { margin: 16px 0 6px; color: #6b2a12; font: bold 12px/18px Tahoma, Arial, sans-serif; }
    .pricing p { margin: 0 0 6px; }
    .pricing ol, .pricing ul { margin: 0 0 6px; padding-left: 20px; }
    .pricing li { margin-bottom: 3px; }
    .pricing table { width: 100%; border-collapse: collapse; margin: 4px 0 8px; }
    .pricing th, .pricing td { padding: 3px 6px; border: 1px solid #c49a63; text-align: left; vertical-align: top; }
    .pricing th { background: rgba(139, 26, 26, .12); color: #6b2a12; }
    .pricing td.num, .pricing th.num { text-align: right; white-space: nowrap; }
    .pricing-premium { font-size: 12px; line-height: 1.45; }
    .pricing__note { color: #7a5a3a; font-style: italic; }
    .pricing__price { color: #8b1a1a; font-weight: bold; }
    .pricing-category { display: flex; align-items: center; gap: 10px; margin: 14px 0 6px; color: #8b1a1a; font: bold 13px/20px Cambria, Georgia, 'Times New Roman', serif; letter-spacing: .06em; text-transform: uppercase; text-shadow: 1px 1px 0 rgba(255, 236, 200, .8); }
    .pricing-category::before, .pricing-category::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, transparent, #a0522d 70%, #8b1a1a); }
    .pricing-category::after { background: linear-gradient(270deg, transparent, #a0522d 70%, #8b1a1a); }
    .pricing-category span { padding: 1px 12px; border: 1px solid #a0522d; border-radius: 10px; background: linear-gradient(180deg, #ffe2b0, #e9b878); box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5), 0 1px 2px rgba(59, 39, 23, .35); }
    .pricing-category span::before, .pricing-category span::after { content: '◆'; margin: 0 6px; color: #a0522d; font-size: 8px; vertical-align: 2px; }
    .pricing-items { display: grid; grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)); gap: 6px; margin: 6px 0 8px; }
    .pricing-item { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px 2px 6px; text-align: center; border: 1px solid #c49a63; background: rgba(255, 236, 200, .35); }
    .pricing-item__name { color: #3b2717; font-weight: bold; line-height: 1.3; overflow-wrap: anywhere; }
    .pricing-item__price { color: #8b1a1a; font-weight: bold; white-space: nowrap; }
    .pricing-item__price small { color: #7a5a3a; font-weight: normal; }
</style>
<article class="b-post b-post--index">
    <div class="b-post__container">
        <div class="b-post__content pricing">
            <h1>Магазин и цены</h1>
            <p>«Вечность» — бесплатная браузерная MMORPG. Играть можно без вложений. За реальные деньги продаётся только внутриигровая валюта — <b>бриллианты</b>. За бриллианты в игре покупаются премиум-статус, предметы премиум-магазина и услуги аккаунта.</p>

            <h2>1. Бриллианты</h2>
            @if($p['priceMinor'] > 0)
                <p>Цена: <span class="pricing__price">1 бриллиант = {{ $usd($p['priceMinor']) }} {{ $p['currency'] }}</span>.
                    @if($p['minimumMinor'] > 0) Минимальная покупка — {{ $usd($p['minimumMinor']) }} {{ $p['currency'] }}.@endif
                    Количество можно указать любое — от минимума до 30 000 бриллиантов.</p>
                <table>
                    <tr><th>Пакет</th><th class="num">Цена</th></tr>
                    @foreach($p['packs'] as $pack)
                        <tr><td>{{ format_money($pack['diamonds']) }} бриллиантов</td><td class="num">{{ $pack['amount'] }} {{ $p['currency'] }}</td></tr>
                    @endforeach
                </table>
            @else
                <p>Цены уточняются.</p>
            @endif

            <h2>2. Как купить бриллианты</h2>
            <ol>
                <li>Зарегистрируйтесь и войдите в игру.</li>
                <li>В премиум-магазине откройте вкладку «Покупка», укажите количество бриллиантов и примите правила.</li>
                <li>Будет создан ордер с фиксированной суммой в {{ $p['currency'] }}. Нажмите «Оплатить криптовалютой» и выберите монету и сеть (USDT, BTC, ETH, TON и другие).</li>
                <li>После подтверждения платежа в сети бриллианты зачисляются на аккаунт автоматически, обычно в течение нескольких минут. Статус ордера виден на его странице и в истории покупок.</li>
            </ol>

            <h2>3. Премиум-статус</h2>
            <p>Премиум-статус покупается за бриллианты. Бриллианты обмениваются на игровые монеты{{ $p['goldPerDiamond'] > 0 ? ' по курсу 1 бриллиант = '.format_money($p['goldPerDiamond']).' монет' : '' }}. Всё, что игрок обменял за 30 дней, суммируется и определяет уровень статуса.</p>
            <ol>
                <li>В премиум-магазине откройте вкладку «Премиум» и укажите, сколько бриллиантов обменять.</li>
                <li>Монеты сразу зачисляются на счёт персонажа, а обменянные бриллианты засчитываются в текущий премиум-период.</li>
                <li>Период длится 30 дней с первого обмена. Новые обмены в течение периода повышают уровень, срок при этом не меняется.</li>
                <li>Когда период закончится, следующий обмен начнёт новый период с первого уровня.</li>
            </ol>
            @if($p['premiumLevels']->isNotEmpty())
                <table class="pricing-premium">
                    <tr><th>Уровень</th><th class="num">Обменять за 30 дней</th><th class="num">Цена</th><th>Бонусы</th></tr>
                    @foreach($p['premiumLevels'] as $level)
                        <tr>
                            <td>Премиум {{ $level->level }}</td>
                            <td class="num">{{ format_money($level->diamonds) }} бр.</td>
                            <td class="num">{{ $p['priceMinor'] > 0 ? $usd($level->diamonds * $p['priceMinor']).' '.$p['currency'] : '—' }}</td>
                            <td>{{ collect([
                                $level->experience ? '+'.$level->experience.'% опыта' : null,
                                $level->money ? '+'.$level->money.'% монет с монстров' : null,
                                $level->gathering ? '+'.$level->gathering.'% скорости сбора' : null,
                                $level->bagSlots ? '+'.$level->bagSlots.' мест в рюкзаке' : null,
                                $level->autoLoot ? 'автосбор добычи' : null,
                                $level->anonymousAuction ? 'анонимный аукцион' : null,
                                $level->anonymousExchange ? 'анонимный обмен' : null,
                            ])->filter()->implode(', ') }}</td>
                        </tr>
                    @endforeach
                </table>
                <p class="pricing__note">Бонусы уровней не суммируются: действует бонус наивысшего достигнутого уровня.</p>
            @endif

            @if($p['categories']->isNotEmpty())
                <h2>4. Премиум-магазин</h2>
                <p>Предметы и услуги, которые покупаются за бриллианты на вкладке «Премиальный магазин»:</p>
                {!! $pricingItemsHtml !!}
                <p class="pricing__note">Наведите на предмет, чтобы увидеть его характеристики.</p>
            @endif

            <h2>5. Доставка, возврат и поддержка</h2>
            <ul>
                <li>Товар цифровой и выдаётся на игровой аккаунт: бриллианты зачисляются автоматически после оплаты, предметы и статус — сразу после покупки в игре.</li>
                <li>Возврат средств возможен при ошибочном или повторном списании, а также если бриллианты не были зачислены. Использованные бриллианты не возвращаются (раздел 5 <a href="{{ route('eula') }}">Лицензионного соглашения</a>).</li>
                <li>Вопросы по оплате: <x-protected-email :email="config('game.support_email')" />. Укажите номер ордера. Ответ — в течение 24 часов. Все контакты — на странице <a href="{{ route('contacts') }}">«Контакты»</a>.</li>
            </ul>
        </div>
    </div>
</article>
