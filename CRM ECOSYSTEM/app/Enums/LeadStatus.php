<?php

namespace App\Enums;

trait Enum {
    public static function fromName(string $name)
    {
        $reflection = new \ReflectionEnum(static::class);
        return $reflection->getCase($name)->getValue();
    }

    public static function tryFromName(string $name)
    {
        $reflection = new \ReflectionEnum(static::class);

        return $reflection->hasCase($name)
            ? $reflection->getCase($name)->getValue()
            : null;
    }
}

enum LeadStatus: int
{
    use Enum;

    case UPGRADE = 2;
    case FILE_OPENING = 5;
}