@extends('admin.layout.base')

@section('title') Документация — Руны и камни @endsection

@section('body')

<style>
    .doc-section { margin-bottom: 32px; }
    .doc-section h3 { font-size: 15px; font-weight: 700; border-bottom: 2px solid #0088cc; padding-bottom: 6px; margin-bottom: 14px; color: #1a2a3a; }
    .doc-section h4 { font-size: 13px; font-weight: 700; color: #444; margin: 14px 0 6px; }
    .doc-section p, .doc-section li { font-size: 12px; color: #555; line-height: 1.7; }
    .doc-section ul { padding-left: 18px; margin-bottom: 8px; }
    .section-intro { background: #f0f7ff; border-left: 4px solid #0088cc; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
    .section-warn { background: #fff5f5; border-left: 4px solid #c0392b; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
    .flow-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 14px 18px; font-size: 12px; margin-bottom: 12px; }
    .flow-box code { background: #e9ecef; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
    .field-table th { font-size: 11px; background: #f0f4f8; }
    .field-table td { font-size: 12px; vertical-align: top; padding: 6px 10px !important; }
    .field-table td:first-child { font-family: monospace; font-weight: 600; color: #c0392b; white-space: nowrap; }
    .badge-note { display: inline-block; background: #fff3cd; color: #856404; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .badge-ok { display: inline-block; background: #e6f4ea; color: #1e7e34; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .model-name { font-family: monospace; font-size: 12px; color: #6a1b9a; background: #f3e5f5; padding: 1px 6px; border-radius: 3px; }
</style>

<div class="row">
    <div class="col-lg-12">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                    <i class="bx bx-diamond" style="font-size:20px;color:#0088cc"></i>
                </div>
                <h2 class="card-title">Руны, рунные ключи, камни и оправы</h2>
                <p class="card-subtitle">Текущая механика кузни: сокеты для камней, рунные слоты, генерация бонусов и ограничения</p>
            </header>
            <div class="card-body">

                <div class="doc-section">
                    <h3><i class="bx bx-map-alt"></i> Где это используется</h3>
                    <div class="section-intro">
                        В кузне есть два разных контура усиления предметов: <b>камни</b> вставляются в сокеты,
                        а <b>руны</b> вплавляются в рунные слоты. Это независимые системы: предмет может иметь и сокеты,
                        и рунные слоты одновременно.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Система</th><th>Что нужно</th><th>Куда ставится</th><th>Что дает</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Камни</td>
                                <td>Оправа + камень</td>
                                <td>Сокеты предмета</td>
                                <td>Фиксированный бонус к одной первичной характеристике</td>
                            </tr>
                            <tr>
                                <td>Руны</td>
                                <td>Рунный ключ + руна</td>
                                <td>Рунные слоты оружия или щита</td>
                                <td>Случайные боевые статы, у эпических/легендарных может выпасть пассивка</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="doc-section">
                    <h3><i class="bx bx-gem"></i> Камни и оправы</h3>

                    <h4>Цикл работы</h4>
                    <div class="flow-box">
                        Предмет <span class="text-primary">→</span> выбрать оправу <span class="text-primary">→</span>
                        установить оправу за монеты <span class="text-primary">→</span> получить случайное число сокетов
                        <span class="text-primary">→</span> вставить камень в пустой сокет.
                    </div>

                    <ul>
                        <li>Оправа расходуется при установке.</li>
                        <li>Стоимость установки списывается с игрока.</li>
                        <li>Максимум сокетов на предмете: <b>4</b>.</li>
                        <li>Если новая оправа дала меньше сокетов, чем уже открыто, предмет не ухудшается, но оправа и монеты тратятся.</li>
                        <li>Камень при вставке расходуется из рюкзака.</li>
                        <li>При извлечении камень возвращается в рюкзак.</li>
                    </ul>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Оправа</th><th>Сокеты</th><th>Стоимость установки</th><th>Цена предмета в сидере</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Обычная</td><td>1</td><td>500 монет</td><td>1000</td></tr>
                            <tr><td>Необычная</td><td>1-2</td><td>1500 монет</td><td>3000</td></tr>
                            <tr><td>Редкая</td><td>2-3</td><td>4000 монет</td><td>8000</td></tr>
                            <tr><td>Эпическая</td><td>2-4</td><td>10000 монет</td><td>20000</td></tr>
                        </tbody>
                    </table>

                    <h4>Самоцветы</h4>
                    <p>
                        Самоцветы дают плоский бонус к одной характеристике:
                        <code>strength</code>, <code>wisdom</code>, <code>agility</code>, <code>intuition</code>, <code>intelligence</code>.
                        Бонус хранится в <code>share_items.gem_stats</code> и применяется в расчете характеристик персонажа.
                    </p>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Тир камня</th><th>Редкость</th><th>Бонус</th><th>Цена</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Малый самоцвет</td><td>Обычный</td><td>+1 к стату</td><td>500</td></tr>
                            <tr><td>Самоцвет</td><td>Необычный</td><td>+2 к стату</td><td>1000</td></tr>
                            <tr><td>Великий самоцвет</td><td>Редкий</td><td>+3 к стату</td><td>1500</td></tr>
                            <tr><td>Большой самоцвет</td><td>Эпический</td><td>+4 к стату</td><td>2000</td></tr>
                            <tr><td>Абсолютный самоцвет</td><td>Легендарный</td><td>+5 к стату</td><td>2500</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="doc-section">
                    <h3><i class="bx bx-key"></i> Руны и рунные ключи</h3>

                    <h4>Цикл работы</h4>
                    <div class="flow-box">
                        Оружие/щит <span class="text-primary">→</span> открыть рунный слот рунным ключом
                        <span class="text-primary">→</span> выбрать пустой слот <span class="text-primary">→</span>
                        вплавить руну <span class="text-primary">→</span> получить случайные статы.
                    </div>

                    <ul>
                        <li>Рунные слоты открываются только на оружии и щитах.</li>
                        <li>Один рунный ключ открывает один слот и расходуется.</li>
                        <li>Максимум рунных слотов на предмете: <b>3</b>.</li>
                        <li>Руна при вплавлении расходуется.</li>
                        <li>Удаленная руна уничтожается и не возвращается в рюкзак.</li>
                        <li>Статы руны генерируются в момент вплавления.</li>
                        <li>Статы руны можно перебросить за золото, стоимость растет после каждого реролла.</li>
                    </ul>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Редкость руны</th><th>Статов</th><th>Множитель</th><th>Пассивка</th><th>Риск-провал</th><th>База реролла</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Обычная</td><td>1-2</td><td>x1.0</td><td>нет</td><td>0%</td><td>100 золота</td></tr>
                            <tr><td>Редкая</td><td>2-3</td><td>x1.8</td><td>нет</td><td>15%</td><td>300 золота</td></tr>
                            <tr><td>Эпическая</td><td>3-4</td><td>x3.0</td><td>25%</td><td>30%</td><td>800 золота</td></tr>
                            <tr><td>Легендарная</td><td>4-5</td><td>x5.0</td><td>60%</td><td>45%</td><td>2000 золота</td></tr>
                        </tbody>
                    </table>

                    <h4>Пул статов руны</h4>
                    <p>Базовые диапазоны умножаются на множитель редкости. В риск-режиме нижняя граница ролла поднимается до 75% диапазона, но включается шанс провала.</p>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Стат</th><th>Базовый диапазон</th><th>Комментарий</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>attack</td><td>3-8</td><td>Плоский бонус к атаке</td></tr>
                            <tr><td>armor</td><td>5-12</td><td>Плоский бонус к броне</td></tr>
                            <tr><td>hp_max</td><td>20-50</td><td>Максимальное здоровье</td></tr>
                            <tr><td>mp_max</td><td>15-35</td><td>Максимальная мана</td></tr>
                            <tr><td>strength</td><td>2-5</td><td>Сила</td></tr>
                            <tr><td>agility</td><td>2-5</td><td>Ловкость</td></tr>
                            <tr><td>intelligence</td><td>2-5</td><td>Интеллект</td></tr>
                            <tr><td>critical</td><td>1-4</td><td>Крит</td></tr>
                            <tr><td>dodge</td><td>1-4</td><td>Уворот</td></tr>
                        </tbody>
                    </table>

                    <h4>Темы рун</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Тема</th><th>Возможные статы</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Огонь</td><td><code>attack</code></td></tr>
                            <tr><td>Лед</td><td><code>armor</code>, <code>hp_max</code></td></tr>
                            <tr><td>Молния</td><td><code>agility</code>, <code>critical</code></td></tr>
                            <tr><td>Тень</td><td><code>dodge</code>, <code>critical</code>, <code>attack</code></td></tr>
                            <tr><td>Земля</td><td><code>strength</code>, <code>armor</code>, <code>hp_max</code></td></tr>
                            <tr><td>Свет</td><td><code>hp_max</code>, <code>mp_max</code>, <code>intelligence</code></td></tr>
                            <tr><td>Хаос</td><td>Полный пул статов</td></tr>
                        </tbody>
                    </table>

                    <div class="section-warn">
                        <b>Текущий статус пассивок:</b> пассивные эффекты рун генерируются, сохраняются и выводятся в интерфейсе кузни,
                        но в боевой системе сейчас применяются только стат-бонусы рун. Перед балансировкой пассивок их нужно отдельно
                        подключить к расчету боя.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Пассивка</th><th>Диапазон</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Вампиризм</td><td>8-15%</td><td>Шанс восстановить 10% нанесенного урона</td></tr>
                            <tr><td>Цепная атака</td><td>15-25%</td><td>Шанс ударить второго врага за 50% урона</td></tr>
                            <tr><td>Оглушение</td><td>8-15%</td><td>Шанс оглушить цель на 1 ход</td></tr>
                            <tr><td>Двойной удар</td><td>12-20%</td><td>Шанс ударить дважды</td></tr>
                            <tr><td>Отражение</td><td>5-10%</td><td>Отражает процент входящего урона</td></tr>
                            <tr><td>Ярость</td><td>15-25%</td><td>Увеличивает урон при HP ниже 30%</td></tr>
                            <tr><td>Щит</td><td>8-15%</td><td>Шанс полностью заблокировать входящий удар</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="doc-section">
                    <h3><i class="bx bx-code-alt"></i> Где смотреть в коде</h3>
                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Часть</th><th>Файл/класс</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Камни</td><td><span class="model-name">GemService</span>, <span class="model-name">GemSeeder</span>, <span class="model-name">PlayerStatService</span></td></tr>
                            <tr><td>Оправы</td><td><span class="model-name">MountRarityConfig</span>, <span class="model-name">GemService::openSocket()</span></td></tr>
                            <tr><td>Руны</td><td><span class="model-name">RuneService</span>, <span class="model-name">RuneSeeder</span>, <span class="model-name">RuneRarity</span></td></tr>
                            <tr><td>Пассивки рун</td><td><span class="model-name">RunePassiveType</span></td></tr>
                            <tr><td>Статы в персонаже</td><td><span class="model-name">PlayerStatService</span></td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

@endsection
