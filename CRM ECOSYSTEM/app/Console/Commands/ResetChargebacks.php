<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Agent;
use Illuminate\Console\Command;

class ResetChargebacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:chargebacks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = date('Y-m', strtotime('-1 month'));
        $fromDate = strtotime(date("$date-27 03:00:00"));
        $fromDate = date('Y-m-d H:i:s', $fromDate);

        $agents = Agent::where('chargebacks', '!=', 0)
            ->withSum(['payments' => function($query) use($fromDate) {
                return $query
                    ->where('completed_at', '>=', $fromDate)
                    ->where('status', PaymentStatus::SUCCESSFUL);
            }], 'amount')
            ->get();

        
        foreach($agents as $agent) {
            $agent->update([
                'chargebacks' => $agent->payments_sum_amount < $agent->chargebacks
                    ? $agent->chargebacks - $agent->payments_sum_amount
                    : 0,
            ]);
        }
    }
}
