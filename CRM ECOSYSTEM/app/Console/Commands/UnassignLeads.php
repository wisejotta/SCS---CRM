<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Lead;
use Illuminate\Console\Command;

class UnassignLeads extends Command
{
    protected $signature = 'lead:unassign';

    protected $description = 'Unassign leads.';

    public function handle()
    {
        $leads = Lead::whereNotNull('agent_id')->get();

        $ids = [];
        foreach($leads as $lead) {
            if((!$lead->callback || $lead->callback < now())) {
                $ids[] = $lead->id;
            }
        }

        Lead::whereIn('id', $ids)->update([
            'agent_id' => null,
            'callback' => null,
            'unassigned_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
