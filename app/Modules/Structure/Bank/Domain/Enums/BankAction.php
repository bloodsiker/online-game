<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Enums;

enum BankAction: string
{
    case DEPOSIT      = 'deposit';
    case WITHDRAW     = 'withdraw';
    case TRANSFER_OUT = 'transfer_out';
    case TRANSFER_IN  = 'transfer_in';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT      => 'Пополнение',
            self::WITHDRAW     => 'Снятие',
            self::TRANSFER_OUT => 'Перевод (отправка)',
            self::TRANSFER_IN  => 'Перевод (получение)',
        };
    }

    public function isIncoming(): bool
    {
        return $this === self::DEPOSIT || $this === self::TRANSFER_IN;
    }
}