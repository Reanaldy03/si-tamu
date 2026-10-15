<?php

namespace App\Domain\Access\Enums;

enum RoleCode: string
{
    case ADMIN = 'ADMIN';
    case STAFF = 'STAFF';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff Front-Office',
        };
    }
}
