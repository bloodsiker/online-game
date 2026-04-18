<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/hero.css') }}">
    <style>
        body {
            margin: 0;
            color: #f4d06e;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }

        .card-money {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-image: url('/img/bg/bgg.gif');
            padding: 3px 12px;
            border-radius: 10px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
            font-size: 11px;
        }

    </style>
</head>
<body>

<table width="220" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="center"></td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
            <div class="player-card">
                <div class="card-header">
                    <div class="level-info">
                        <div class="level-number" id="playerLevel">{{ $page->player->lvl }}</div>
                    </div>

                    <div class="stats-bars">
                        <div class="stat-bar">
                            <div class="stat-fill hp-fill" id="hpBar" style="width: {{ $page->player->getPercentHp() }}%"></div>
                            <div class="stat-text" id="hpText">{{ $page->player->hp_now }} / {{ $page->playerDecorator->getHpMax() }}</div>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill mana-fill" id="manaBar" style="width: {{ $page->player->getPercentMp() }}%"></div>
                            <div class="stat-text" id="manaText">{{ $page->player->mp_now }} / {{ $page->playerDecorator->getMpMax() }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-money">
                    <b class="redd">
                        <span title="Монеты">
                            <img src="{{ asset('img/icon/m_game.gif') }}" alt="Монеты" width="11" height="11" align="absmiddle">
                        </span>
                        <span id="playerMoney">
                              {{ format_money($page->player->user->money) }}
                        </span>
                    </b>
                    <b class="redd">
                        <span title="Бриллиант">
                            <img src="{{ asset('img/icon/m_dmd.gif') }}" alt="Бриллиант" width="11" height="11" align="absmiddle">
                        </span>
                        <span id="playerDiamond">
                            {{ format_money($page->player->user->diamond) }}
                        </span>
                    </b>
                </div>

                <div class="avatar-container">
                    <div class="avatar-frame">
                        <img src="{{ asset('img/avatar/dark_elf.jpg') }}" alt="Аватар гравця" class="avatar-img" id="avatarImg">
                    </div>
                </div>

                <div class="experience-section">
                    <div class="exp-body">
                        <div class="exp-bar">
                            <div class="exp-fill" id="expBar" style="width: {{ $page->player->getPercentExp() }}%"></div>
                        </div>
                        <span class="exp-percentage" id="expPercentage">{{ $page->player->getPercentExp() }}%</span>
                    </div>

                </div>

                <div class="cooldown-section">
                    <div class="cooldown-bar">
                        <div class="cooldown-fill" id="cooldownBar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
        <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>

<br>

<div id="effectsContainer" style="display:none; margin-top: 4px;">
    <div class="effects-container">
        <div id="blessingsSection" class="effects-section">
            <div class="effects-list" id="blessingsList"></div>
        </div>
        <div id="cursesSection" class="effects-section curses" style="margin-top: 3px;">
            <div class="effects-list" id="cursesList"></div>
        </div>
    </div>
</div>

<script>
    // Симуляція отримання пошкодження
    function simulateDamage() {
        // Перевірка чи кнопка на cooldown
        if (parent.isCooldown) {
            return;
        }

        // Запуск cooldown
        startCooldown();

        const hpBar = document.getElementById('hpBar');
        const hpText = document.getElementById('hpText');
        const currentHP = parseInt(hpText.textContent.split(' / ')[0]);
        const maxHP = parseInt(hpText.textContent.split(' / ')[1]);

        const damage = Math.floor(Math.random() * 50) + 20;
        const newHP = Math.max(0, currentHP - damage);
        const hpPercentage = (newHP / maxHP) * 100;

        hpBar.style.width = hpPercentage + '%';
        hpText.textContent = `${newHP} / ${maxHP}`;

        // Симуляція витрати мани
        const manaBar = document.getElementById('manaBar');
        const manaText = document.getElementById('manaText');
        const currentMana = parseInt(manaText.textContent.split(' / ')[0]);
        const maxMana = parseInt(manaText.textContent.split(' / ')[1]);

        const manaCost = Math.floor(Math.random() * 30) + 10;
        const newMana = Math.max(0, currentMana - manaCost);
        const manaPercentage = (newMana / maxMana) * 100;

        manaBar.style.width = manaPercentage + '%';
        manaText.textContent = `${newMana} / ${maxMana}`;

        // Додавання досвіду
        const expBar = document.getElementById('expBar');
        const expPercentage = document.getElementById('expPercentage');
        const currentExp = parseFloat(expPercentage.textContent);
        const newExp = Math.min(100, currentExp + Math.random() * 5);

        expBar.style.width = newExp + '%';
        expPercentage.textContent = newExp.toFixed(1) + '%';

        // Перевірка на level up
        if (newExp >= 100) {
            levelUp();
        }
    }

    function startCooldown() {
        parent.startCooldown();
    }

    function levelUp(newLevel) {
        const levelElement = document.getElementById('playerLevel');
        const currentLevel = parseInt(levelElement.textContent);

        // Створюємо клон елемента для анімації старого рівня
        const levelClone = levelElement.cloneNode(true);
        levelClone.style.position = 'absolute';
        levelClone.style.top = '0';
        levelClone.style.top = '50%';
        levelClone.style.transform = 'translateX(-50%)';
        levelClone.classList.add('level-up-animation');

        // Додаємо клон до батьківського елемента
        levelElement.parentElement.style.position = 'relative';
        levelElement.parentElement.appendChild(levelClone);

        // Ховаємо оригінальний елемент на час анімації
        levelElement.style.opacity = '0';

        // Через невеликий час показуємо новий рівень
        setTimeout(() => {
            levelElement.textContent = newLevel;
            levelElement.style.opacity = '1';
            levelElement.classList.add('new-level-appear');

            // Видаляємо клон після завершення анімації
            setTimeout(() => {
                if (levelClone.parentElement) {
                    levelClone.parentElement.removeChild(levelClone);
                }
                levelElement.classList.remove('new-level-appear');
            }, 800);
        }, 600);

        // Скидання досвіду
        document.getElementById('expBar').style.width = '0%';
        document.getElementById('expPercentage').textContent = '0.0%';
    }

    // Поступове відновлення ресурсів
    // setInterval(() => {
    //     // Відновлення HP
    //     const hpBar = document.getElementById('hpBar');
    //     const hpText = document.getElementById('hpText');
    //     const [currentHP, maxHP] = hpText.textContent.split(' / ').map(Number);
    //
    //     if (currentHP < maxHP) {
    //         const newHP = Math.min(maxHP, currentHP + 2);
    //         hpBar.style.width = ((newHP / maxHP) * 100) + '%';
    //         hpText.textContent = `${newHP} / ${maxHP}`;
    //     }
    //
    //     // Відновлення мани
    //     const manaBar = document.getElementById('manaBar');
    //     const manaText = document.getElementById('manaText');
    //     const [currentMana, maxMana] = manaText.textContent.split(' / ').map(Number);
    //
    //     if (currentMana < maxMana) {
    //         const newMana = Math.min(maxMana, currentMana + 3);
    //         manaBar.style.width = ((newMana / maxMana) * 100) + '%';
    //         manaText.textContent = `${newMana} / ${maxMana}`;
    //     }
    // }, 2000);




    // ============================================
    // СИСТЕМА БЛАГОСЛОВЕНЬ ТА ПРОКЛЯТЬ
    // ============================================

    const activeEffects = {
        blessings: new Map(),
        curses: new Map()
    };

    // Ініціалізація
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('cooldownBar').style.width = '100%';

        @foreach($page->activeEffects as $effect)
        @if($effect->duration > 0)
        {{ $effect->isCurse ? 'addCurse' : 'addBlessing' }}(
            '{{ $effect->id }}',
            '{{ addslashes($effect->name) }}',
            {{ $effect->duration }}
        );
        @endif
        @endforeach
    });

    // Оновити видимість секцій
    function updateSectionsVisibility() {
        const container = document.getElementById('effectsContainer');
        const blessingsSection = document.getElementById('blessingsSection');
        const cursesSection = document.getElementById('cursesSection');

        const hasBlessings = activeEffects.blessings.size > 0;
        const hasCurses = activeEffects.curses.size > 0;

        // Показуємо/ховаємо секції
        blessingsSection.style.display = hasBlessings ? 'block' : 'none';
        cursesSection.style.display = hasCurses ? 'block' : 'none';

        // Показуємо контейнер тільки якщо є хоча б один ефект
        container.style.display = (hasBlessings || hasCurses) ? 'block' : 'none';
    }

    // Додати благословення
    function addBlessing(id, name, duration) {
        const endTime = Date.now() + (duration * 1000);

        activeEffects.blessings.set(id, {
            id,
            name,
            endTime,
            duration: duration * 1000
        });

        updateSectionsVisibility();
        renderBlessings();
        startEffectTimer(id, 'blessing');
    }

    // Додати прокляття
    function addCurse(id, name, duration) {
        const endTime = Date.now() + (duration * 1000);

        activeEffects.curses.set(id, {
            id,
            name,
            endTime,
            duration: duration * 1000
        });

        updateSectionsVisibility();
        renderCurses();
        startEffectTimer(id, 'curse');
    }

    // Видалити ефект
    function removeEffect(id, type) {
        const element = document.querySelector(`[data-effect-id="${id}"]`);

        if (element) {
            element.classList.add('removing');
            setInterval(() => {
                element.remove();
            }, 300)

            setTimeout(() => {
                if (type === 'blessing') {
                    activeEffects.blessings.delete(id);
                } else {
                    activeEffects.curses.delete(id);
                }

                if (type === 'blessing') {
                    renderBlessings();
                } else {
                    renderCurses();
                }

                updateSectionsVisibility();
            }, 300);
        }
    }

    // Відрендерити благословення
    function renderBlessings() {
        const container = document.getElementById('blessingsList');
        const blessings = Array.from(activeEffects.blessings.values());

        // Додаємо нові елементи
        blessings.forEach(blessing => {
            if (!container.querySelector(`[data-effect-id="${blessing.id}"]:not(.removing)`)) {
                const element = createEffectElement(blessing);
                container.appendChild(element);
            }
        });
    }

    // Відрендерити прокляття
    function renderCurses() {
        const container = document.getElementById('cursesList');
        const curses = Array.from(activeEffects.curses.values());

        curses.forEach(curse => {
            if (!container.querySelector(`[data-effect-id="${curse.id}"]:not(.removing)`)) {
                const element = createEffectElement(curse);
                container.appendChild(element);
            }
        });
    }

    // Створити HTML елемент ефекту
    function formatEffectTime(s) {
        if (s >= 3600) return Math.floor(s / 3600) + 'ч ' + Math.floor((s % 3600) / 60) + 'м';
        if (s >= 60)   return Math.floor(s / 60) + 'м ' + (s % 60) + 'с';
        return s + 'с';
    }

    function createEffectElement(effect) {
        const div = document.createElement('div');
        div.className = 'effect-item';
        div.dataset.effectId = effect.id;

        const remaining = Math.max(0, Math.ceil((effect.endTime - Date.now()) / 1000));
        const timerHtml = remaining > 0
            ? `<span class="effect-timer${remaining <= 10 ? ' warning' : ''}" data-timer-id="${effect.id}">${formatEffectTime(remaining)}</span>`
            : '';

        div.innerHTML = `<span class="effect-name">${effect.name}</span>${timerHtml}`;

        return div;
    }

    // Запустити таймер для ефекту
    function startEffectTimer(id, type) {
        const effect = type === 'blessing'
            ? activeEffects.blessings.get(id)
            : activeEffects.curses.get(id);

        if (!effect) return;

        const updateTimer = () => {
            const remaining = Math.max(0, Math.ceil((effect.endTime - Date.now()) / 1000));
            const timerElement = document.querySelector(`[data-timer-id="${id}"]`);
            if (!timerElement) return;

            timerElement.textContent = formatEffectTime(remaining);
            remaining <= 10 ? timerElement.classList.add('warning') : timerElement.classList.remove('warning');

            if (remaining <= 0) {
                removeEffect(id, type);
            } else {
                requestAnimationFrame(updateTimer);
            }
        };

        requestAnimationFrame(updateTimer);
    }

    // ============================================
    // END СИСТЕМА БЛАГОСЛОВЕНЬ ТА ПРОКЛЯТЬ
    // ============================================



    // Обработка полученных данных
    window.addEventListener('message', function(event) {
        // console.log('Получены данные в character:', event.data);
        const { hp, mp, experience, lvl, blessing, curse, money, diamond } = event.data;

        if (hp !== undefined) {
            const hpBar = document.getElementById('hpBar');
            const hpText = document.getElementById('hpText');
            hpBar.style.width = ((hp.current / hp.max) * 100) + '%';
            hpText.textContent = `${hp.current} / ${hp.max}`;
        }

        if (mp !== undefined) {
            const manaBar = document.getElementById('manaBar');
            const manaText = document.getElementById('manaText');
            manaBar.style.width = ((mp.current / mp.max) * 100) + '%';
            manaText.textContent = `${mp.current} / ${mp.max}`;
        }

        if (money !== undefined) {
            const playerMoney = document.getElementById('playerMoney');
            playerMoney.textContent = money;
        }

        if (diamond !== undefined) {
            const playerDiamond = document.getElementById('playerDiamond');
            playerDiamond.textContent = diamond;
        }

        if (lvl !== undefined) {
            const levelElement = document.getElementById('playerLevel');
            const currentLevel = parseInt(levelElement.textContent);
            if (lvl > currentLevel) {
                levelUp(lvl);
                return;
            }
        }

        if (experience !== undefined) {
            const expBar = document.getElementById('expBar');
            const expPercentage = document.getElementById('expPercentage');

            expBar.style.width = experience + '%';
            expPercentage.textContent = experience + '%';
        }

        if (blessing !== undefined) {
            addBlessing(blessing.id, blessing.name, blessing.duration);
        }

        if (curse !== undefined) {
            addCurse(curse.id, curse.name, curse.duration);
        }
    });
</script>

</body>
</html>
