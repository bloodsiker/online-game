<?php

declare(strict_types=1);

return [
    'status' => [
        'invite' => 'ожидание согласия',
        'request' => 'заявка на вступление',
        'accepted' => 'принято',
        'declined' => 'отклонено',
        'cancelled' => 'отменено',
    ],
    'directory' => [
        'title' => 'Рейтинг кланов',
        'rank' => 'Место',
        'clan' => 'Клан',
        'level' => 'Уровень',
        'members' => 'Игроков',
        'experience' => 'Опыт',
        'leader' => 'Глава',
        'actions' => 'Действие',
        'apply' => 'Подать заявку',
        'pending' => 'Заявка отправлена',
        'pending_elsewhere' => 'Уже есть активная заявка',
        'invited' => 'Есть приглашение',
        'withdraw' => 'Отозвать',
        'location_required' => 'Создать клан можно только у регистратора на локации 29.',
        'empty' => 'Кланы ещё не созданы.',
        'full' => 'Набор закрыт',
    ],
    'application' => [
        'submitted' => 'Заявка отправлена главе клана.',
        'accepted' => 'Игрок принят в клан.',
        'declined' => 'Заявка отклонена.',
        'withdrawn' => 'Заявка отозвана.',
        'accept' => 'Принять',
        'decline' => 'Отклонить',
        'mail_subject' => 'Новая заявка в клан «:clan»',
        'mail_title' => 'Заявка на вступление в клан',
        'mail_greeting' => 'Здравствуйте, :name!',
        'mail_body' => 'Персонаж :applicant [:level] подал заявку на вступление в клан «:clan».',
        'mail_button' => 'Открыть состав клана',
        'mail_footer' => 'Заявку можно рассмотреть в разделе состава клана.',
    ],
    'requests' => [
        'heading' => 'Заявки и приглашения',
    ],
];
