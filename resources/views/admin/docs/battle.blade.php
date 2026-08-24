@extends('admin.layout.base')

@section('title') Документация — Боевой треугольник и характеристики @endsection

@section('body')

<style>
    .doc-section { margin-bottom: 36px; }
    .doc-section h3 { font-size: 15px; font-weight: 700; border-bottom: 2px solid #0088cc; padding-bottom: 6px; margin-bottom: 14px; color: #1a2a3a; }
    .doc-section h4 { font-size: 13px; font-weight: 700; color: #444; margin: 14px 0 6px; }
    .doc-section p, .doc-section li { font-size: 12px; color: #555; line-height: 1.7; }
    .doc-section ul { padding-left: 18px; margin-bottom: 8px; }
    .field-table th { font-size: 11px; background: #f0f4f8; }
    .field-table td { font-size: 12px; vertical-align: top; padding: 6px 10px !important; }
    .field-table td:first-child { font-family: monospace; font-weight: 600; color: #c0392b; white-space: nowrap; }
    .field-table td:nth-child(2) { color: #888; font-size: 11px; white-space: nowrap; }
    .badge-enum { display: inline-block; background: #e8f0fe; color: #1967d2; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-family: monospace; margin: 1px; }
    .badge-note { display: inline-block; background: #fff3cd; color: #856404; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .badge-warn { display: inline-block; background: #fde8e8; color: #a3272f; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .flow-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 14px 18px; font-size: 12px; margin-bottom: 12px; }
    .flow-box code { background: #e9ecef; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
    .arrow { color: #0088cc; font-weight: bold; margin: 0 4px; }
    .tbl-name { font-family: monospace; font-size: 13px; font-weight: 700; color: #0d47a1; background: #e8f0fe; padding: 2px 8px; border-radius: 4px; }
    .model-name { font-family: monospace; font-size: 12px; color: #6a1b9a; background: #f3e5f5; padding: 1px 6px; border-radius: 3px; }
    .section-intro { background: #f0f7ff; border-left: 4px solid #0088cc; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
    .section-warn { background: #fff5f5; border-left: 4px solid #c0392b; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
    .const-table td:first-child { font-family: monospace; font-weight: 600; color: #6a1b9a; white-space: nowrap; }
    .const-table td:nth-child(2) { font-family: monospace; font-weight: 700; color: #c0392b; white-space: nowrap; }
    .triangle-edge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
    .edge-tank { background: #e3ecfa; color: #2a4a8a; }
    .edge-dodge { background: #e3f7ea; color: #2a7a4a; }
    .edge-crit { background: #fae3e3; color: #8a2a2a; }
</style>

<div class="row">
    <div class="col-lg-12">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                    <i class="bx bx-shield-quarter" style="font-size:20px;color:#0088cc"></i>
                </div>
                <h2 class="card-title">Боевой треугольник и характеристики — документация</h2>
                <p class="card-subtitle">Как считается урон, как работает контра классов, что даёт каждая стата, инструменты проверки баланса</p>
            </header>
            <div class="card-body">

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ОБЩАЯ СХЕМА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-git-branch"></i> Идея треугольника</h3>

                    <div class="section-intro">
                        Боевой класс персонажа (и монстра) — <b>не выбирается</b>, а определяется автоматически по тому,
                        куда вложены очки характеристик. Три класса контрят друг друга по кругу — как камень-ножницы-бумага.
                    </div>

                    <p style="text-align:center; margin: 14px 0;">
                        <span class="triangle-edge edge-tank">Танк</span>
                        <span class="arrow">бьёт</span>
                        <span class="triangle-edge edge-dodge">Уворот</span>
                        <span class="arrow">бьёт</span>
                        <span class="triangle-edge edge-crit">Крит</span>
                        <span class="arrow">бьёт</span>
                        <span class="triangle-edge edge-tank">Танк</span>
                    </p>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Ребро</th><th>Механизм</th><th>Где в коде</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Танк <span class="arrow">›</span> Уворот</td>
                                <td>Танковость атакующего продавливает шанс уворота защитника (срезает до <b>27%</b> от максимума)</td>
                                <td class="model-name">HitCalculator::isDodge()</td>
                            </tr>
                            <tr>
                                <td>Уворот <span class="arrow">›</span> Крит</td>
                                <td>Уворотливость защитника сбивает шанс крита атакующего (срезает до <b>5%</b>)</td>
                                <td class="model-name">HitCalculator::isCritical()</td>
                            </tr>
                            <tr>
                                <td>Крит <span class="arrow">›</span> Танк</td>
                                <td>Критовость атакующего пробивает броню защитника на крит. ударе (срезает до <b>92%</b> брони)</td>
                                <td class="model-name">HitCalculator::effectiveArmor()</td>
                            </tr>
                        </tbody>
                    </table>

                    <p>
                        Сила контры зависит не от того, «какой у вас класс» дискретно, а от того, <b>насколько чисто</b> вы
                        в него вложились — это называется <b>«доля класса сверх базовой трети»</b>:
                    </p>

                    <div class="flow-box">
                        <code>classShareAboveBaseline = max(0, (доля_класса − 1/3) / (2/3))</code><br><br>
                        Универсальный билд (доли ≈ 33/33/33%) <span class="arrow">→</span> доля-сверх-базы = <b>0%</b> → контра не работает вообще<br>
                        Гибрид (доля ≈ 55%) <span class="arrow">→</span> доля-сверх-базы ≈ <b>33%</b> от максимума контры<br>
                        Чистый билд (доля ≈ 90%) <span class="arrow">→</span> доля-сверх-базы ≈ <b>85%</b> от максимума контры
                    </div>

                    <p>
                        Это защищает «универсалов» от одновременного получения бонусов всех трёх контр — контра начинает
                        работать только когда класс выражен заметно сильнее среднего.
                    </p>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ХАРАКТЕРИСТИКИ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-slider"></i> Характеристики персонажа</h3>

                    <div class="section-intro">
                        6 первичных характеристик, очки на них выдаются за уровень (5 свободных + расовые автостаты) и
                        распределяются игроком на <span class="tbl-name">/character/points</span>. Класс определяют только
                        <b>сила / ловкость / интуиция</b> — мудрость, интеллект и выносливость на класс не влияют.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr><th>Стата</th><th>Класс</th><th>Что даёт</th><th>Формула</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Сила</td>
                                <td><span class="badge-enum">strength</span></td>
                                <td>Броня (митигация урона) + % к урону оружия (с софткапом)</td>
                                <td>
                                    Броня: <code>+1 за очко</code><br>
                                    Урон: <code>strengthDamagePercent()</code>, софткап 100%
                                </td>
                            </tr>
                            <tr>
                                <td>Ловкость</td>
                                <td><span class="badge-enum">agility</span></td>
                                <td>Шанс уворота от вражеских атак</td>
                                <td><code>+1 к уворот-стату за очко</code></td>
                            </tr>
                            <tr>
                                <td>Интуиция</td>
                                <td><span class="badge-enum">intuition</span></td>
                                <td>Шанс крит. удара + сила крит. урона (с софткапом)</td>
                                <td>
                                    Шанс: <code>+1 к крит-стату за очко</code><br>
                                    Критурон: <code>critDamageBonus()</code>, софткап 300%
                                </td>
                            </tr>
                            <tr>
                                <td>Мудрость</td>
                                <td><span class="badge-note">не боевая</span></td>
                                <td>Максимум маны и магическое сопротивление</td>
                                <td><code>mp_max = 10 + 3 × (мудрость − 1)</code><br><code>magic_resistance = мудрость − 1</code> + бонусы экипировки</td>
                            </tr>
                            <tr>
                                <td>Интеллект</td>
                                <td><span class="badge-note">не боевая</span></td>
                                <td>Сила магической атаки</td>
                                <td><code>magic_power = intelligence + magic_attack</code>, где <code>magic_attack</code> — бонусы экипировки</td>
                            </tr>
                            <tr>
                                <td>Выносливость</td>
                                <td><span class="badge-warn">без софткапа</span></td>
                                <td>Максимум HP поверх уровневой базы</td>
                                <td><code>+3 HP за очко</code>, растёт линейно без предела</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Максимум HP игрока</h4>
                    <div class="flow-box">
                        <code>hp_max = 10 + 12 × (уровень − 1) + 3 × (выносливость − 1)</code>
                    </div>
                    <p>
                        HP <b>не зависит от силы</b> — так было не всегда: раньше HP росло от силы, и это делало Танка
                        победителем во всех матчапах (сила разом давала HP + броню + урон). Перенос HP на уровень +
                        отдельную стату «Выносливость» вернул треугольник к жизни.
                        <span class="badge-warn">Выносливость — единственная стата без софткапа</span> — сознательный выбор
                        (см. предупреждение ниже).
                    </p>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ФОРМУЛЫ БОЯ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-calculator"></i> Формулы боя (HitCalculator)</h3>

                    <h4>Константы (актуальные значения в коде)</h4>
                    <table class="table table-bordered table-hover const-table" style="max-width:520px;">
                        <tbody>
                            <tr><td>BASE_CHANCE</td><td>5%</td></tr>
                            <tr><td>MIN_CHANCE</td><td>5%</td></tr>
                            <tr><td>MAX_STAT_BONUS</td><td>70%</td></tr>
                            <tr><td>SOFTCAP_K (на 12 lvl)</td><td>55</td></tr>
                            <tr><td>ARMOR_CONSTANT (на 12 lvl)</td><td>220</td></tr>
                            <tr><td>CRIT_DAMAGE_BASE</td><td>175%</td></tr>
                            <tr><td>CRIT_DAMAGE_CAP</td><td>300%</td></tr>
                            <tr><td>DODGE_COUNTER</td><td>0.27</td></tr>
                            <tr><td>CRIT_COUNTER</td><td>0.05</td></tr>
                            <tr><td>ARMOR_PIERCE</td><td>0.92</td></tr>
                        </tbody>
                    </table>

                    <h4>Шанс уворота / крита от разницы стат</h4>
                    <div class="flow-box">
                        <code>chance = max(5%, 5% + 70% × Δ / (|Δ| + K))</code>, где Δ — разница нужной статы атакующего и защитника
                    </div>
                    <p>Пример при уровне 12 (K=55) — как растёт шанс при перевесе в статах:</p>
                    <table class="table table-bordered table-hover field-table" style="max-width:460px;">
                        <thead><tr><th>Δ (перевес)</th><th>Шанс</th></tr></thead>
                        <tbody>
                            <tr><td>0 (статы равны)</td><td>5%</td></tr>
                            <tr><td>27</td><td>≈28%</td></tr>
                            <tr><td>55</td><td>40%</td></tr>
                            <tr><td>110</td><td>≈52%</td></tr>
                            <tr><td>220</td><td>61%</td></tr>
                            <tr><td>500</td><td>≈68%</td></tr>
                            <tr><td>→ ∞</td><td>→ 75% (потолок)</td></tr>
                        </tbody>
                    </table>
                    <p>
                        <span class="badge-note">Важно</span> формула несимметрична у нуля: любое <b>отставание</b> (Δ &lt; 0)
                        почти сразу проваливается в нижний порог 5% — отдача от «догоняющих» очков нелинейна, значение имеет
                        в первую очередь то, кто <b>впереди</b>.
                    </p>

                    <h4>Митигация от брони</h4>
                    <div class="flow-box">
                        <code>итоговый_урон = урон × ARMOR_CONSTANT / (ARMOR_CONSTANT + броня)</code>
                    </div>
                    <table class="table table-bordered table-hover field-table" style="max-width:400px;">
                        <thead><tr><th>Броня</th><th>Митигация</th></tr></thead>
                        <tbody>
                            <tr><td>0</td><td>0%</td></tr>
                            <tr><td>110</td><td>33%</td></tr>
                            <tr><td>220</td><td>50%</td></tr>
                            <tr><td>440</td><td>67%</td></tr>
                            <tr><td>880</td><td>80%</td></tr>
                            <tr><td>→ ∞</td><td>→ 100% (никогда не достигается)</td></tr>
                        </tbody>
                    </table>
                    <p>На крит. ударе атакующего класса «Крит» броня защитника уменьшается (пробитие) ещё до применения этой формулы.</p>

                    <h4>Масштаб по уровню</h4>
                    <div class="flow-box">
                        <code>levelScale = max(1, средний_уровень_сторон / 12)</code><br>
                        K и ARMOR_CONSTANT умножаются на <code>levelScale</code> — форма кривых (в процентах) не меняется
                        от 1 до 500 уровня, только абсолютные цифры характеристик растут вместе с уровнем.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- МАГИЧЕСКАЯ АТАКА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-atom"></i> Магическая атака и сопротивление</h3>

                    <div class="section-intro">
                        Магический урон используют атакующие заклинания игрока и монстры с типом атаки
                        <span class="badge-enum">magic</span>. Для него применяется отдельный
                        <code>MagicHitCalculator</code>: магический удар не может промахнуться, стать критическим,
                        быть заблокирован щитом или отражён руной. Единственная защита цели — магическое сопротивление.
                    </div>

                    <h4>Формула урона заклинания игрока</h4>
                    <div class="flow-box">
                        <code>сила_магии = интеллект + magic_attack экипировки</code><br>
                        <code>сырой_урон = random(min_damage, max_damage) + round(сила_магии × power_coefficient)</code><br>
                        <code>A = 220 × max(1, средний_уровень_сторон ÷ 12)</code><br>
                        <code>итоговый_урон = max(1, round(сырой_урон × A ÷ (A + magic_resistance цели)))</code>
                    </div>
                    <p>
                        <code>min_damage</code>, <code>max_damage</code> и <code>power_coefficient</code> задаются отдельно
                        у каждого заклинания. Интеллект — базовая часть силы магии; свойство предмета
                        <code>magic_attack</code> добавляется к ней. Мудрость повышает ману и магическое сопротивление,
                        а предметы могут дать дополнительное <code>magic_resistance</code>.
                    </p>

                    <h4>Магические атаки монстров</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле моба</th><th>Назначение</th><th>Как используется</th></tr></thead>
                        <tbody>
                            <tr><td>attack_type</td><td>Тип обычной атаки</td><td><span class="badge-enum">physical</span> — физическая атака; <span class="badge-enum">magic</span> — магическая</td></tr>
                            <tr><td>min_dmg / max_dmg</td><td>Базовый диапазон</td><td>Используется для обоих типов атак как <code>random(min_dmg, max_dmg)</code></td></tr>
                            <tr><td>magic_attack</td><td>Сила магии моба</td><td>Добавляется к базовому диапазону только при магической атаке</td></tr>
                            <tr><td>magic_power_coefficient</td><td>Коэффициент силы магии</td><td><code>magic_attack × magic_power_coefficient</code>; при <code>0</code> магическая атака наносит только базовый диапазон</td></tr>
                            <tr><td>magic_resistance</td><td>Защита от заклинаний</td><td>Снижает только магический урон. Базовое значение для обычного моба — <code>1 × уровень</code>, но его можно задать индивидуально.</td></tr>
                        </tbody>
                    </table>
                    <p>
                        У мобов нет интеллекта, поэтому их сила магии состоит только из поля <code>magic_attack</code>.
                        Их магическое сопротивление задаётся отдельно и не влияет на физическую защиту.
                        Для босса специальный навык может переопределить тип урона через <code>damage_type</code> и
                        коэффициент через <code>magic_power_coefficient</code>. Перед сохранением моба с магической атакой
                        следует проверить его против персонажа с разным <code>magic_resistance</code>.
                    </p>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ПРЕДУПРЕЖДЕНИЯ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-error"></i> Известные особенности и ограничения</h3>

                    <div class="section-warn">
                        <b>Выносливость не капается.</b> Все боевые статы (шанс уворота/крита, урон от силы, критурон) имеют
                        софткап — рост замедляется и практически останавливается после разумного вложения очков. HP от
                        выносливости растёт <b>линейно без ограничения</b>. Симуляция (<code>battle:simulate-pve</code>,
                        <code>battle:simulate --hp-mode=game</code>) показала: билд, вкладывающий большую часть очков в
                        одну выносливость, при коэффициенте <code>HP_PER_ENDURANCE=6</code> выигрывал у всех архетипов
                        88–100%. Текущее значение снижено до <b>3</b> — доминирование заметно слабее, но полностью не
                        исчезло. Если понадобится дожать баланс — варианты: софткап на выносливость (как у остальных стат)
                        или требования к предметам по статам (см. ниже).
                    </div>

                    <div class="section-warn">
                        <b>Требования к предметам сейчас не заведены.</b> В системе есть механизм
                        <span class="model-name">ShareItemRequirement</span> (тип <span class="badge-enum">stat</span> —
                        требуемый уровень характеристики для ношения вещи), но ни на одном предмете в базе требований нет.
                        Это значит, что персонаж, вложивший все очки в выносливость, может носить точно такой же шмот, как
                        и «правильно» прокачанный — никакого штрафа за отказ от боевой статы. Если наполнить требования
                        (сила/ловкость/интуиция растущие по тиру предмета), билды с нулевой боевой статой естественным
                        образом потеряют доступ к сильному оружию и броне.
                    </div>

                    <div class="section-warn">
                        <b>Собственная ловкость атакующего — это ещё и «меткость».</b> Формула уворота сравнивает уворот
                        защитника с уворотом <i>атакующего</i> — то есть высокая ловкость помогает не только уворачиваться,
                        но и точнее попадать по чужому уворотy, независимо от классовой контры «Танк › Уворот». В «разбавленных»
                        билдах (не 90% в стату, а 50–60%) этот сырой эффект иногда перевешивает классовую контру — учитывайте
                        это, выставляя монстрам уворот/крит: важно не только соотношение броня/уворот/крит (оно определяет
                        «класс» монстра), но и абсолютное значение относительно типичного статбюджета игрока на этом уровне.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- МОНСТРЫ И ОПЫТ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-ghost"></i> Калибровка монстров и таблицы опыта</h3>

                    <div class="section-intro">
                        Раньше и монстры, и таблица опыта (<span class="model-name">experiences</span>) вбивались на
                        глаз — шаг между уровнями рос произвольно, а таблица обрывалась на 50 уровне. Теперь оба
                        генерируются формулами (<span class="model-name">MonsterStatFormulas</span>,
                        <span class="model-name">ExperienceCurve</span>), так что новый контент ложится на ту же
                        кривую, а не собирается заново каждый раз руками.
                    </div>

                    <h4>Характеристики монстра по уровню — <span class="model-name">MonsterStatFormulas</span></h4>
                    <p>Каждая характеристика — функция уровня и «профиля вида» (набора целевых % от статов
                        HitCalculator), а не число с потолка:</p>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Стата</th><th>Формула</th><th>Смысл параметра вида</th></tr></thead>
                        <tbody>
                            <tr><td>hp</td><td>(10 + 8×(уровень−1)) × hpMultiplier</td><td>множитель «живучести» вида (1.0 у мыши, 1.7-2.8 у медведя)</td></tr>
                            <tr><td>armor</td><td>ARMOR_CONSTANT(уровень) × m ÷ (1−m)</td><td>m — целевая доля урона, гасимая бронёй (0.00 у мыши, 0.21 у медведя)</td></tr>
                            <tr><td>dodge / critical</td><td>SOFTCAP_K(уровень) × f ÷ (1−f), f=(%−5)÷70</td><td>целевой % шанса уворота/крита вида</td></tr>
                            <tr><td>min_dmg / max_dmg</td><td>avg = (%÷100)×(10+12×(уровень−1)); min=0.7×avg, max=1.3×avg</td><td>% — доля HP игрока того же уровня, теряемая за один удар</td></tr>
                            <tr><td>exp</td><td>40 × уровень^1.5 × difficultyMultiplier</td><td>опыт за полное соло-убийство того же уровня (см. ниже почему это чистое число)</td></tr>
                            <tr><td>min_money / max_money</td><td>exp × 0.15 / exp × 0.30</td><td>—</td></tr>
                        </tbody>
                    </table>
                    <div class="section-warn">
                        <b>ARMOR_CONSTANT/SOFTCAP_K сами имеют пол <code>levelScale=max(1, уровень÷12)</code></b>
                        (см. HitCalculator) — то есть на уровнях 1-12 константы не уменьшаются, и сырые статы под один
                        и тот же целевой % шанса/митигации одинаковы независимо от уровня внутри этого диапазона. При
                        добавлении монстров выше 12 уровня сырые статы для того же % начнут расти пропорционально
                        уровню — это ожидаемо, но нужно закладывать явно.
                    </div>

                    <h4>8 откалиброванных мобов (проверены <span class="tbl-name">battle:simulate-pve</span>)</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Моб</th><th>Ур.</th><th>HP</th><th>Броня</th><th>Урон</th><th>Опыт</th><th>Победа игрока (типовой билд)</th></tr></thead>
                        <tbody>
                            <tr><td>Мышь</td><td>1</td><td>10</td><td>0</td><td>1-2</td><td>10</td><td>100%, ~1 раунд — тривиальный первый моб</td></tr>
                            <tr><td>Летучая мышь</td><td>2</td><td>23</td><td>12</td><td>2-3</td><td>31</td><td>100%, ~2.5 раунда</td></tr>
                            <tr><td>Волк</td><td>4</td><td>61</td><td>30</td><td>4-8</td><td>104</td><td>~99.5-100%, ~6-9 раундов — уже настоящий бой</td></tr>
                            <tr><td>Медведь</td><td>7</td><td>99</td><td>58</td><td>7-14</td><td>296</td><td>~94-98%, ~9-11 раундов — сложный, но проходимый</td></tr>
                            <tr><td>Кабан</td><td>10</td><td>135</td><td>32</td><td>9-17</td><td>442</td><td>~98-100%, ~10-13 раундов</td></tr>
                            <tr><td>Разбойник</td><td>13</td><td>140</td><td>19</td><td>12-22</td><td>610</td><td>~99.8-100%, ~9-11 раундов — уворот/крит вместо брони</td></tr>
                            <tr><td>Тролль</td><td>16</td><td>225</td><td>62</td><td>15-27</td><td>1088</td><td>~88-98%, ~12-15 раундов — первый по-настоящему тяжёлый моб</td></tr>
                            <tr><td>Огр</td><td>20</td><td>249</td><td>62</td><td>21-39</td><td>1699</td><td>~89-98%, ~10-14 раундов</td></tr>
                        </tbody>
                    </table>
                    <div class="section-warn">
                        <b>Медведь изначально не проходился (0% побед) при hpMultiplier=2.8/dmgPercent=20%</b> — HP и
                        урон одновременно завышенные превратили его в непроходимого «супертанка»: игрок не успевал
                        нанести достаточно урона до того, как умирал сам. Исправлено эмпирически через
                        <code>battle:simulate-pve</code> (hpMultiplier→1.7, armorMitigation→0.21, dmgPercent→13%) —
                        сначала считать формулой, но обязательно проверять симуляцией, а не доверять числу вслепую.
                    </div>
                    <div class="section-warn">
                        <b>Тот же самый баг повторился на Кабане/Тролле/Огре (уровни 10, 16, 20 — выше REFERENCE_LEVEL=12)</b> —
                        первая прикидка параметров дала 0-40% побед (Тролль/Огр) или 20-30% (Кабан). Причина та же:
                        выше 12 уровня <code>levelScale</code> начинает расти, и параметры, скопированные «в лоб» с
                        мобов 1-7 уровня, оказались завышены сразу по HP и урону. Пришлось итеративно прогонять
                        <code>battle:simulate-pve --level= --hp= --min-dmg= --max-dmg= --armor= --dodge= --critical=</code>
                        руками (без сохранения в БД) 3-4 раза на каждого моба, пока не сошлось на 88-100% — только
                        потом обратные параметры (hpMultiplier/armorMitigation/dmgPercent) зафиксированы в коде.
                        Мораль та же: формула — стартовая точка, симуляция — обязательная проверка, особенно на
                        уровнях выше 12, где начинает работать levelScale.
                    </div>

                    <h4>Опыт за удар — почему <code>monster.exp</code> это просто число</h4>
                    <p><span class="model-name">AttackService::calculateExperience()</span>: опыт раздаётся
                        пропорционально нанесённому урону от максимального HP монстра
                        (<code>damage × monster.exp ÷ monsterMaxHp</code>), с множителем ±5% за каждый уровень разницы
                        (капается 0.01×-2.0×). Раз урон считается по HP-доле, сумма опыта за раунды при полном
                        соло-убийстве монстра <b>того же уровня</b> точно равна <code>monster.exp</code> — это и есть
                        «опыт за одно убийство», из которого потом считается таблица опыта.</p>

                    <h4>Таблица опыта — <span class="model-name">ExperienceCurve</span></h4>
                    <p>Требование на уровень = «опыт с одного убийства типового моба своего уровня» × «сколько таких
                        убийств нужно для левел-апа». Опыт с моба — по-прежнему гладкая формула
                        <code>referenceMonsterExp(L) = 10 × L^1.5</code>, а вот <code>killsPerLevel(L)</code> теперь
                        <b>не одна формула на весь диапазон, а три сегмента</b> — так удалось совместить «быстрый вход
                        для новичка» и «эндгейм на годы», которые математически несовместимы при единой гладкой кривой
                        (900 уровней после 100-го при той же сложности, что и сам 100-й, уже сами по себе дают
                        десятилетия — см. ниже).</p>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Сегмент</th><th>killsPerLevel(L)</th><th>Смысл</th></tr></thead>
                        <tbody>
                            <tr><td>1-20</td><td>15 + ⌊L÷3⌋</td><td>быстрый вход, почти не меняется (15 → 21 убийство) — новичок не должен застревать</td></tr>
                            <tr><td>20-100</td><td>экспоненциальная интерполяция 21 → 1870</td><td>«стена»: разгон от лёгкого фарма к настоящей игре</td></tr>
                            <tr><td>100-1000</td><td>экспоненциальная интерполяция 1870 → 3000</td><td>пологое эндгейм-плато — тяжелее, но без резких скачков</td></tr>
                        </tbody>
                    </table>
                    <p>Целевой темп (при грубом ориентире ~2ч активного гринда/день, ~10 сек/убийство —
                        см. <code>battle:simulate-pve</code> для реального числа раундов на бой): <b>~1.5 месяца до
                        100 уровня, ~8 лет до 1000-го</b>. Опорные точки 1870 и 3000 подобраны именно под эти два
                        целевых срока — это не круглые числа, а результат подгонки под конкретный запрос по длительности игры.</p>

                    <div class="section-warn">
                        <b>Почему не одна гладкая формула на весь диапазон.</b> Если бы 100 уровень должен был занимать
                        3-5 месяцев (более долгая веха), это уже означало бы ~6000+ убийств на один левел-ап в этом
                        районе — а тогда оставшиеся 900 уровней даже БЕЗ дальнейшего роста сложности дают
                        900×6000 ≈ 21+ год. «3-5 месяцев на 100-й» и «5-10 лет на 1000-й» одновременно невозможны при
                        неубывающей сложности — пришлось сознательно выбрать «100-й уровень — быстрая веха (1.5 мес.),
                        1000-й — многолетний эндгейм», а не наоборот.
                    </div>
                    <div class="section-warn">
                        <b>Коэффициент опыта общий с <code>MonsterStatFormulas::expReward()</code></b> — один источник
                        правды (<code>ExperienceCurve::referenceMonsterExp</code>), чтобы опыт монстров и таблица
                        уровней не могли разойтись при будущих правках.
                    </div>
                    <div class="section-warn">
                        <b>Таблица сейчас сгенерирована только до 100 уровня (безопасно, cumulative ≈ 244 млн) —
                        дальше расширять НЕЛЬЗЯ без миграции в bigInteger.</b> Из-за резкого разгона на сегменте
                        20-100 (killsPerLevel вырос с 48 до 1870 по сравнению с прежней линейной кривой) cumulative
                        exp переполняет int32 уже примерно на <b>165 уровне</b> — было ~450-500 при прежней, более
                        пологой формуле. Колонки <code>experiences.exp</code>, <code>experiences.exp_diff</code> и
                        <code>players.exp</code>/<code>exp_up</code>/<code>exp_diff</code> нужно будет мигрировать в
                        <code>bigInteger</code> ДО того, как расширять таблицу дальше 100 уровня, а не «когда-нибудь
                        потом» — запас стал заметно меньше.
                    </div>
                    <div class="section-warn">
                        <b>Фиксированные EXP-награды квестов пересчитаны пропорционально (÷4).</b> В квестах (обычных,
                        клановых, репутационных) есть награды опыта как <b>абсолютное число</b>, не завязанное на
                        формулу монстров, — при смене коэффициента 40→10 их пришлось обновить отдельно (65 строк в БД
                        + <span class="model-name">QuestSeeder</span>/<span class="model-name">ClanQuestSeeder</span>/<span class="model-name">ReputationSeeder</span>/<span class="model-name">GenerateSeed</span>),
                        иначе они стали бы в 4 раза «мощнее» относительно гринда. Если опыт монстров ещё раз
                        масштабировать — не забыть про эти награды.
                    </div>

                    <h4>Применение к уже работающей базе — <span class="tbl-name">game:recalibrate-leveling</span></h4>
                    <p>В отличие от <span class="model-name">GenerateSeed</span> (только для чистой установки), эта
                        команда безопасна для базы с реальными игроками: пересчитывает <span class="model-name">experiences</span>
                        целиком, обновляет/создаёт 4 стартовых моба по имени и сбрасывает прогресс-бар игроков внутри
                        их <b>текущего</b> уровня под новую кривую — уровень никому не меняется, теряется только
                        накопленный процент до следующего левел-апа.</p>
                    <div class="flow-box">
                        <code>php artisan game:recalibrate-leveling</code><br>
                        <code>php artisan game:recalibrate-leveling --max-level=200</code> — если позже расширить таблицу
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ИНСТРУМЕНТЫ ПРОВЕРКИ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-terminal"></i> Инструменты проверки баланса</h3>

                    <p>Два artisan-команды в модуле <span class="model-name">Battle</span> для симуляции боёв без реальных игроков.</p>

                    <h4>PvP-матрица: <span class="tbl-name">battle:simulate</span></h4>
                    <p>Дуэли синтетических билдов друг с другом, матрица винрейтов по парам. Хороший результат — рёбра
                        треугольника в диапазоне ~60–75%, без разрывов вида 95%+ у одного билда против всех.</p>
                    <div class="flow-box">
                        <code>php artisan battle:simulate --hp-mode=game --levels=12,50,100,200,300,400,500</code><br>
                        <code>php artisan battle:simulate --hp-mode=game --realistic --levels=12,100,500</code>
                    </div>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Опция</th><th>Значение</th></tr></thead>
                        <tbody>
                            <tr><td>--hp-mode=game</td><td>реальная формула HP (уровень + выносливость), 6 билдов с разным вложением в выносливость</td></tr>
                            <tr><td>--realistic</td><td>билды с силой у всех (под требования вещей), без выносливости</td></tr>
                            <tr><td>--levels=</td><td>список уровней через запятую — бюджет стат считается как 8 × уровень</td></tr>
                            <tr><td>--fights=</td><td>число дуэлей на пару билдов (по умолчанию 2000)</td></tr>
                        </tbody>
                    </table>

                    <h4>PvE-проверка монстра: <span class="tbl-name">battle:simulate-pve</span></h4>
                    <p>Типовые билды игрока против конкретного монстра — своими руками заданными статами или уже
                        существующего в базе. Показывает % побед и среднее число раундов — по числу раундов видно контру
                        класса, даже если побеждают все билды.</p>
                    <div class="flow-box">
                        <code>php artisan battle:simulate-pve --level=50 --hp=700 --min-dmg=25 --max-dmg=35 --armor=150</code><br>
                        <code>php artisan battle:simulate-pve --id=7</code> — статы берутся из уже созданного монстра
                    </div>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Опция</th><th>Значение</th></tr></thead>
                        <tbody>
                            <tr><td>--id=</td><td>ID монстра из базы — остальные статы игнорируются, тянутся из БД</td></tr>
                            <tr><td>--level, --hp, --min-dmg, --max-dmg</td><td>основные параметры монстра, если не через --id</td></tr>
                            <tr><td>--armor, --dodge, --critical</td><td>защитные статы — их соотношение определяет класс монстра (Танк/Уворот/Крит)</td></tr>
                            <tr><td>--player-level=</td><td>уровень игрока, если отличается от уровня монстра</td></tr>
                            <tr><td>--max-rounds=</td><td>потолок раундов — если бой не завершился, это «ничья» (сигнал, что монстр практически непробиваем)</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

@endsection
