<?php

namespace App\Enums;

enum CustomerStatus: int
{
    case ACTIVE = 0;
    case INACTIVE = 1;
}