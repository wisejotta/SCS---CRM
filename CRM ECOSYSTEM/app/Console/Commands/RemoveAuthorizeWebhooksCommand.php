<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RemoveAuthorizeWebhooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes old authorize.net webhooks.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $payments = Payment::where('status', '!=', PaymentStatus::SUCCESSFUL)
            ->whereNotNull('misc->webhook')
            ->where('created_at', '<', date('Y-m-d H:i:s', \strtotime('-1 hour')))
            ->get();

        foreach($payments as $payment) {
            $baseUrl = config('authorize.baseUrl');
            $merchantName  = config('authorize.merchantName');
            $transactionKey  = config('authorize.transactionKey');
            $webhookId = $payment->misc['webhook'];

            $response = Http::withBasicAuth($merchantName, $transactionKey)
                ->delete("$baseUrl/rest/v1/webhooks/$webhookId");
            if(!$response->successful()) {
                \logger()->error('Error deleting webhook:' . $response->body());
            } else {
                $misc = $payment->misc;
                unset($misc['webhook']);
                $payment->update([
                   'misc' => $misc,
                ]);
            }
        }
    }
}
