<?php

namespace App\Domain\Visits\Enums;

enum RegistrationSource: string
{
    case PUBLIC = 'PUBLIC';
    case STAFF = 'STAFF';
    case IMPORT = 'IMPORT';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Mandiri (Publik)',
            self::STAFF => 'Petugas (Front-Office)',
            self::IMPORT => 'Impor Data',
        };
    }
}
