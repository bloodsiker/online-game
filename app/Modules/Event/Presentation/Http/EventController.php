<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http;

use App\Modules\Event\Application\DTOs\ActivityCardDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class EventController
{
    private const string BONUS_CLAN_TOKEN = '<b><a href="#" style="color:#3300ff;" onclick="return false;">Жетон «Опора клана»</a></b>';

    public function index(Request $request): View
    {
        $mode = (string) $request->query('mode', 'events');
        if (! in_array($mode, ['events', 'events_future', 'events_my', 'activity', 'rewards'], strict: true)) {
            $mode = 'events';
        }

        $group = (string) $request->query('group', 'daily');
        if (! in_array($group, ['daily', 'weekly'], strict: true)) {
            $group = 'daily';
        }

        return view('event::index', [
            'mode' => $mode,
            'group' => $group,
            'activities' => $group === 'weekly' ? $this->weeklyActivities() : $this->dailyActivities(),
        ]);
    }

    private function diamondBonus(string $amount): string
    {
        return '<span title="Диамант"><img src="/img/icon/m_dmd.gif" width="11" height="11" align="absmiddle"></span>&nbsp;'.$amount;
    }

    /** @return Collection<int, ActivityCardDTO> */
    private function dailyActivities(): Collection
    {
        return collect([
            new ActivityCardDTO(
                title: 'Паук-патриарх',
                icon: 'img/event/ebg_tank_4.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 2,
                descriptionHtml: 'Убейте Паука-патриарха <strong><span style="color:green">2</span></strong> раза',
            ),
            new ActivityCardDTO(
                title: 'Крэтс-мясник',
                icon: 'img/event/59199_3_18_121954_drakon.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 1,
                descriptionHtml: 'Убейте Крэтса-мясника <strong><span style="color:green">1</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Уничтожение Цербера',
                icon: 'img/event/chaoselement_081121.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 3,
                descriptionHtml: 'Уничтожьте Цербера <strong><span style="color:green">3</span></strong> раза',
                bonusHtml: $this->diamondBonus('0.05'),
            ),
            new ActivityCardDTO(
                title: 'Хаотичные битвы',
                icon: 'img/event/glad_shop_coin.gif',
                iconCount: 2,
                progressCurrent: 0,
                progressTotal: 5,
                descriptionHtml: 'Одержите победу в <strong><span style="color:darkred">хаотичной</span></strong> битве <strong><span style="color:green">5</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Рейдовые боссы',
                icon: 'img/event/green_obrivok.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 10,
                descriptionHtml: 'Убейте рейдовых боссов <strong><span style="color:darkred">1-5 уровня рейда</span></strong> <strong><span style="color:green">10</span></strong> раз',
                bonusHtml: self::BONUS_CLAN_TOKEN,
            ),
            new ActivityCardDTO(
                title: 'Уничтожение монстров',
                icon: 'img/event/gift_coffee_can.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 100,
                descriptionHtml: '<span style="color:blue"><strong>Убейте 100 монстров.</strong></span>',
                bonusHtml: self::BONUS_CLAN_TOKEN,
            ),
            new ActivityCardDTO(
                title: 'Уничтожение монстров II',
                icon: 'img/event/ebg_tank_4.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 200,
                descriptionHtml: '<strong><span style="color:red">Убейте 200 монстров.</span></strong>',
                bonusHtml: $this->diamondBonus('0.10'),
            ),
            new ActivityCardDTO(
                title: 'Добыча ресурсов',
                icon: 'img/event/drakon_vestniki.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 100,
                descriptionHtml: '<span style="color:blue"><strong>Соберите 100 любых ресурсов</strong></span>',
                bonusHtml: self::BONUS_CLAN_TOKEN,
            ),
            new ActivityCardDTO(
                title: 'Добыча ресурсов II',
                icon: 'img/event/ebg_tank_4.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 200,
                descriptionHtml: '<strong><span style="color:red">Соберите 200 любых ресурсов</span></strong>',
                bonusHtml: $this->diamondBonus('0.10'),
            ),
        ]);
    }

    /** @return Collection<int, ActivityCardDTO> */
    private function weeklyActivities(): Collection
    {
        return collect([
            new ActivityCardDTO(
                title: 'Коллекция боссов',
                icon: 'img/event/karta_GREY1.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 10,
                descriptionHtml: 'Скрафтите любую из <strong><span style="color:darkred">коллекций боссов</span></strong> <strong><span style="color:green">10</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Коллекция «Мастер ресурсов»',
                icon: 'img/event/badge_karta_fio.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 8,
                descriptionHtml: 'Скрафтите жетон <strong><span style="color:darkred">«Мастер ресурсов»</span></strong> <strong><span style="color:green">8</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Коллекция «Талисман могущества»',
                icon: 'img/event/sertif_bril_10.gif',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 10,
                descriptionHtml: 'Скрафтите коллекцию <strong><span style="color:darkred">Талисман могущества</span></strong> <strong><span style="color:green">10</span></strong> раз',
                bonusHtml: $this->diamondBonus('0.25'),
            ),
            new ActivityCardDTO(
                title: 'Коллекция блага по классам',
                icon: 'img/event/chest_blaga_default.png',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 15,
                descriptionHtml: 'Скрафтите любую из коллекций <strong><span style="color:darkred">Блага по классам</span></strong> <strong><span style="color:green">15</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Коллекция — Ртуть',
                icon: 'img/event/prem_leg2.png',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 10,
                descriptionHtml: 'Скрафтите Алхимическую посуду Избранных из коллекции <strong><span style="color:darkred">Ртуть</span></strong> <strong><span style="color:green">10</span></strong> раз',
            ),
            new ActivityCardDTO(
                title: 'Инстанс Майя',
                icon: 'img/event/chest_blaga_default.png',
                iconCount: 1,
                progressCurrent: 0,
                progressTotal: 5,
                descriptionHtml: 'Пройдите инстанс <strong><span style="color:darkred">Майя</span></strong> <strong><span style="color:green">5</span></strong> раз',
                bonusHtml: self::BONUS_CLAN_TOKEN,
            ),
        ]);
    }
}