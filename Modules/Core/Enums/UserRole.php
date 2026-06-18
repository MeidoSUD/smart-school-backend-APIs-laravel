<?php

namespace Modules\Core\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Parent = 'parent';
    case Teacher = 'teacher';
    case Staff = 'staff';
    case Accountant = 'accountant';
    case Librarian = 'librarian';
    case Admin = 'admin';
    case Guest = 'guest';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public static function staffRoles(): array
    {
        return [
            self::Teacher,
            self::Staff,
            self::Accountant,
            self::Librarian,
        ];
    }
}
