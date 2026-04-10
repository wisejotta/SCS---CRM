<?php

namespace App\Enums;

use App\Models\Visa;
use Illuminate\Database\Eloquent\Collection;

enum AgentType: int
{
    case UPGRADE = 2;
    case LEAD_ASSIGNER = 4;
    case BACK_OFFICE = 5;
    case CUSTOMER_SERVICE = 6;
    case FILE_OPENING = 7;

    public function products(): Collection
    {
        return match($this)
        {
            AgentType::FILE_OPENING => Visa::whereIn('id', [10, 11, 12, 13, 16, 17, 18])->orderBy('order', 'asc')->get(),
            AgentType::UPGRADE => Visa::whereNotIn('id', [10, 11, 12, 13, 16, 17, 18])->orderBy('order', 'asc')->get(),
            AgentType::LEAD_ASSIGNER => Visa::orderBy('order', 'asc')->get(),
            AgentType::BACK_OFFICE => Visa::orderBy('order', 'asc')->get(),
            AgentType::CUSTOMER_SERVICE => Visa::orderBy('order', 'asc')->get(),
        };
    }
}