<?php

declare(strict_types=1);

return [
    'status' => [
        'invite' => 'очікує на згоду',
        'request' => 'заявка на вступ',
        'accepted' => 'прийнято',
        'declined' => 'відхилено',
        'cancelled' => 'скасовано',
    ],
    'directory' => [
        'title' => 'Рейтинг кланів',
        'rank' => 'Місце',
        'clan' => 'Клан',
        'level' => 'Рівень',
        'members' => 'Гравців',
        'experience' => 'Досвід',
        'leader' => 'Лідер',
        'actions' => 'Дія',
        'apply' => 'Подати заявку',
        'pending' => 'Заявку надіслано',
        'pending_elsewhere' => 'Вже є активна заявка',
        'invited' => 'Є запрошення',
        'withdraw' => 'Відкликати',
        'location_required' => 'Створити клан можна лише у реєстратора на локації 29.',
        'empty' => 'Клани ще не створені.',
        'full' => 'Набір закрито',
    ],
    'application' => [
        'submitted' => 'Заявку надіслано лідеру клану.',
        'accepted' => 'Гравця прийнято до клану.',
        'declined' => 'Заявку відхилено.',
        'withdrawn' => 'Заявку відкликано.',
        'accept' => 'Прийняти',
        'decline' => 'Відхилити',
        'mail_subject' => 'Нова заявка до клану «:clan»',
        'mail_title' => 'Заявка на вступ до клану',
        'mail_greeting' => 'Вітаємо, :name!',
        'mail_body' => 'Персонаж :applicant [:level] подав заявку на вступ до клану «:clan».',
        'mail_button' => 'Переглянути склад клану',
        'mail_footer' => 'Заявку можна розглянути в розділі складу клану.',
    ],
    'requests' => [
        'heading' => 'Заявки та запрошення',
    ],
];
