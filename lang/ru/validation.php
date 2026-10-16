<?php

declare(strict_types=1);

// Preserve Laravel messages for rules outside this localization stage.
return array_replace_recursive(require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php'), [
    'required' => 'Поле «:attribute» обязательно.',
    'string' => 'Поле «:attribute» должно быть строкой.',
    'boolean' => 'Поле «:attribute» должно быть логическим значением.',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'in' => 'Выбрано недопустимое значение поля «:attribute».',
    'confirmed' => 'Подтверждение поля «:attribute» не совпадает.',
    'current_password' => 'Текущий пароль указан неверно.',
    'different' => 'Поля «:attribute» и «:other» должны отличаться.',
    'date' => 'Поле «:attribute» должно содержать корректную дату.',
    'date_format' => 'Поле «:attribute» должно соответствовать формату :format.',
    'between' => ['numeric' => 'Поле «:attribute» должно быть от :min до :max.', 'string' => 'Поле «:attribute» должно содержать от :min до :max символов.'],
    'min' => ['numeric' => 'Поле «:attribute» должно быть не меньше :min.', 'string' => 'Поле «:attribute» должно содержать минимум :min символов.'],
    'max' => ['numeric' => 'Поле «:attribute» должно быть не больше :max.', 'string' => 'Поле «:attribute» должно содержать не более :max символов.'],
    'attributes' => [
        'language' => 'Язык / Мова',
        'volume' => 'Громкость',
        'sound_mention' => 'Обращение ко мне',
        'sound_private' => 'Приват',
        'sound_clan' => 'Клан',
        'sound_trade' => 'Торговля',
        'sound_battle' => 'Начало боя',
        'sound_dungeon' => 'Вход в подземелье',
        'sound_mail' => 'Новая почта',
        'sound_gathering' => 'Сбор ресурса',
        'show_map' => 'Показывать карту',
        'show_hotbar' => 'Показывать панель быстрого доступа',
        'show_item_tooltips' => 'Подсказки предметов',
        'chat_sound_enabled' => 'Включить звук уведомлений чата',
        'mute_open_chat' => 'Без звука при открытом чате',
    ],
]);
