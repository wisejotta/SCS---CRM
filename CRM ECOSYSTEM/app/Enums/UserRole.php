<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 0;
    case AGENT = 3;
    case CUSTOMER = 4;
}