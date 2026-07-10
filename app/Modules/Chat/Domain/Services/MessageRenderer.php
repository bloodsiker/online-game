<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Services;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class MessageRenderer
{
    /** Единый список смайлов: используется и рендерером, и панелью выбора */
    public const SMILES = [
        ':bee:' => 'bee.gif',
        ':detstvo:' => 'beeee.gif',
        ':cool:' => 'cool.gif',
        ':dance:' => 'dance.gif',
        ':eda:' => 'eda.gif',
        ':fight:' => 'fight.gif',
        ':fuu:' => 'fuu.gif',
        ':grrr:' => 'grrrr.gif',
        ':hi:' => 'hi.gif',
        ':jump:' => 'jump.gif',
        ':lol:' => 'lol.gif',
        ':love:' => 'love.gif',
        ':love1:' => 'love1.gif',
        ':love2:' => 'love2.gif',
        ':mol:' => 'mol.gif',
        ':money:' => 'money.gif',
        ':no:' => 'no.gif',
        ':nono:' => 'nono.gif',
        ':podozr:' => 'podozren.gif',
        ':rose:' => 'rose.gif',
        ':rr:' => 'rr.gif',
        ':smile:' => 'smile.gif',
        ':son:' => 'son.gif',
        ':speak:' => 'speak.gif',
        ':ogo:' => 'ogo.gif',
        ':bitva:' => 'bitva.gif',
        ':cry:' => 'cry.gif',
        ':dragon:' => 'dragon.gif',
        ':buee:' => 'buee.gif',
        ':drink:' => 'drink.gif',
        ':horse:' => 'horse.gif',
        ':karate:' => 'karate.gif',
        ':king:' => 'king.gif',
        ':kiss:' => 'kiss.gif',
        ':knight1:' => 'knight1.gif',
        ':kaldun:' => 'koldun.gif',
        ':ninza:' => 'ninza.gif',
        ':peace:' => 'peace.gif',
        ':stesn:' => 'stesn.gif',
        ':wizard:' => 'wizard.gif',
        ':pobit:' => 'beatenup3.gif',
        ':red:' => 'embarrassed2.gif',
        ':devil:' => 'sarcastic.gif',
        ':ponder:' => 'ponder2.gif',
        ':wink:' => 'winking2.gif',
        ':shok:' => 'shok.gif',
        ':dragon1:' => 'drag_vesna.gif',
        ':sama:' => '_sama.gif',
        ':krapiva:' => 'krapiva_l2.gif',
        ':kaena:' => 'kaena.gif',
        ':duck:' => 'duck.gif',
        ':burglar:' => 'burglar2.gif',
        ':krokokot:' => 'krokokot.gif',
        ':buba:' => 'buba.gif',
        ':bell:' => 'smile_bell.gif',
        ':angel:' => 'angel.gif',
        ':bayan:' => 'bayan.gif',
        ':drop:' => 'drop.gif',
        ':gift:' => 'gift.gif',
        ':sneak:' => 'sneak.gif',
        ':wall:' => 'wall.gif',
        ':zol:' => 'zol.gif',
        ':bolen:' => 'dwbolen.gif',
        ':flag:' => 'dwflag.gif',
        ':hugs:' => 'dwhugs2.gif',
        ':upal:' => 'dwupal.gif',
        ':artovik:' => 'artovik1.gif',
        ':nomoney:' => 'nomoney.gif',
        ':goodbye:' => 'goodbye.gif',
        ':shahter:' => 'shahter.gif',
        ':uchitel:' => 'uchitel.gif',
        ':zanoza:' => 'zanoza.gif',
        ':reverans:' => '20723_090908_shoescrapefloor_female2.gif',
        ':salut:' => 'fwrbg.gif',
        ':dealer:' => 'dealer.gif',
        ':market:' => 'market.gif',
        ':valentinka:' => 'smile_valentinka.gif',
        ':govoril:' => '222govoril.gif',
        ':neotrazima:' => '222neotrazima.gif',
    ];

    /**
     * Escape user input and render shortcodes as safe HTML.
     *
     * [[item_ID]]     → styled item name span
     * [[user_ID]]     → player profile link
     * [[money_N]]     → coin icon + amount
     * [[diamond_N]]   → diamond icon + amount
     * to[NAME]        → styled mention prefix
     * :smile:         → smile image
     */
    public function render(string $message, bool $trusted = false): string
    {
        // Trusted messages (system/information/quest) contain safe server-generated HTML
        $escaped = $trusted ? $message : e($message);

        // Replace [[item_ID]] shortcodes
        $rendered = preg_replace_callback('/\[\[item_(\d+)\]\]/', function ($matches) {
            $item = Item::with('itemInfo')->find((int) $matches[1]);

            if (! $item || ! $item->itemInfo) {
                return '<span class="chat-item-unknown" title="Предмет не найден">[???]</span>';
            }

            $name = e($item->getName());
            $name .= $item->upgrade_lvl > 0 ? ' +'.$item->upgrade_lvl : '';
            $color = e($item->itemInfo->rarity?->color() ?? '#666666');
            $desc = e($item->itemInfo->description ?? '');

            $infoUrl = route('items.info', ['id' => $item->id]);

            return '<span class="chat-item" style="color:'.$color.'" title="'.$desc.'"'
                .' onclick="window.open(\''.$infoUrl.'\', \'\', \'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'
                .$name.'</span>';
        }, $escaped);

        // Replace [[user_ID]] shortcodes with profile links
        $rendered = preg_replace_callback('/\[\[user_(\d+)\]\]/', function ($matches) {
            $user = User::find((int) $matches[1]);

            if (! $user) {
                return '<span class="chat-item-unknown" title="Персонаж не найден">[???]</span>';
            }

            $url = route('info.user', ['id' => $user->id]);

            return '<a href="'.$url.'" class="chat-user" title="Информация о персонаже"'
                .' onclick="window.open(\''.$url.'\', \'\', \'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'
                .'<b>'.e($user->name).'</b></a>';
        }, $rendered);

        // Replace [[money_N]] / [[diamond_N]] currency shortcodes
        $currencies = [
            'money' => ['icon' => 'img/icon/m_game.gif', 'title' => 'Монеты'],
            'diamond' => ['icon' => 'img/icon/m_dmd.gif', 'title' => 'Диаманты'],
        ];
        foreach ($currencies as $code => $currency) {
            $rendered = preg_replace(
                '/\[\['.$code.'_(\d+)\]\]/',
                '<span title="'.$currency['title'].'"><img src="'.asset($currency['icon'])
                    .'" width="11" height="11" align="absmiddle" alt="'.$currency['title'].'"></span>&nbsp;$1',
                $rendered,
            );
        }

        // Highlight to[NAME] prefix
        $rendered = preg_replace(
            '/^to\[([^\]]+)\]\s*-?\s*/u',
            '<span class="chat-to">»$1</span> ',
            $rendered,
        );

        // Replace smile shortcodes with images
        static $smileMap = null;
        if ($smileMap === null) {
            $smileMap = [];
            foreach (self::SMILES as $code => $file) {
                $smileMap[$code] = '<img src="'.asset('data/smiles/'.$file)
                    .'" class="chat-smile" style="max-height: 20px; vertical-align: middle;" alt="'.$code.'" title="'.$code.'">';
            }
        }

        return strtr($rendered, $smileMap);
    }
}
