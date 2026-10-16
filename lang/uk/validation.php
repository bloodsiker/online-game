<?php

declare(strict_types=1);

// Preserve Laravel messages for rules outside this localization stage.
return array_replace_recursive(require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php'), [
    'required' => 'Поле «:attribute» обов’язкове.',
    'string' => 'Поле «:attribute» має бути рядком.',
    'boolean' => 'Поле «:attribute» має бути логічним значенням.',
    'integer' => 'Поле «:attribute» має бути цілим числом.',
    'in' => 'Вибрано недопустиме значення поля «:attribute».',
    'confirmed' => 'Підтвердження поля «:attribute» не збігається.',
    'current_password' => 'Поточний пароль указано неправильно.',
    'different' => 'Поля «:attribute» та «:other» мають відрізнятися.',
    'date' => 'Поле «:attribute» має містити коректну дату.',
    'date_format' => 'Поле «:attribute» має відповідати формату :format.',
    'between' => ['numeric' => 'Поле «:attribute» має бути від :min до :max.', 'string' => 'Поле «:attribute» має містити від :min до :max символів.'],
    'min' => ['numeric' => 'Поле «:attribute» має бути не менше :min.', 'string' => 'Поле «:attribute» має містити щонайменше :min символів.'],
    'max' => ['numeric' => 'Поле «:attribute» має бути не більше :max.', 'string' => 'Поле «:attribute» має містити не більше :max символів.'],
    'attributes' => [
        'language' => 'Мова / Язык',
        'volume' => 'Гучність',
        'sound_mention' => 'Звернення до мене',
        'sound_private' => 'Приват',
        'sound_clan' => 'Клан',
        'sound_trade' => 'Торгівля',
        'sound_battle' => 'Початок бою',
        'sound_dungeon' => 'Вхід до підземелля',
        'sound_mail' => 'Нова пошта',
        'sound_gathering' => 'Збір ресурсу',
        'show_map' => 'Показувати карту',
        'show_hotbar' => 'Показувати панель швидкого доступу',
        'show_item_tooltips' => 'Підказки предметів',
        'chat_sound_enabled' => 'Увімкнути звук сповіщень чату',
        'mute_open_chat' => 'Без звуку у відкритому чаті',
    ],
]);
