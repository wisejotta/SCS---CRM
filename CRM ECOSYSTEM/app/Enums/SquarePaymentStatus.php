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

enum SquarePaymentStatus: int
{
    use Enum;
    
    case APPROVED = 0;
    case PENDING = 1;
    case COMPLETED = 2;
    case CANCELED = 3;
    case FAILED = 4;
}