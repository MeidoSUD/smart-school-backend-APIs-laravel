<?php

namespace Modules\Core\Enums;

enum Gender: string
{
    case Male = 'Male';
    case Female = 'Female';
    case Other = 'Other';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
