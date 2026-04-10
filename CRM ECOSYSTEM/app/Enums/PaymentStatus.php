<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case SENT = 0;
    case SUCCESSFUL = 1;
    case FAILED = 2;
    case CANCELED = 3;
}