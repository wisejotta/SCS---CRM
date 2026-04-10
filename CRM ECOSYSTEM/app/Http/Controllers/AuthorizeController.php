<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthorizeController extends Controller
{
    public function form(Request $request, Payment $payment) {
        return \view('authorize.form')->with([
            'token' => $payment->misc['token'],
        ]);
    }

    public function webhook(Request $request, Payment $payment) {
        \logger()->info($request->all());

        if($payment->status == PaymentStatus::SUCCESSFUL) return;

        if($payload = $request->post('payload')) {
            $baseUrl  = config('authorize.baseUrl');
            $merchantName  = config('authorize.merchantName');
            $transactionKey  = config('authorize.transactionKey');
            $transId = $payload['id'];

            $response = Http::post("$baseUrl/xml/v1/request.api", [
                'getTransactionDetailsRequest' => [
                    'merchantAuthentication' => [
                        'name' => $merchantName,
                        'transactionKey' => $transactionKey,
                    ],
                    'transId' => $transId,
                ],
            ]);

            if($response->successful()) {
                $body = \explode('{"transaction"', $response->body());
                $details = \json_decode('{"transaction"' . $body[1], true);
                $transaction = $details['transaction'];
                $message = $transaction['responseReasonDescription'];
                if($transaction['responseCode'] == 1) {
                    $title = 'Payment successful';

                    if($paymentDetails = $transaction['payment']) {
                        if($creditCard = $paymentDetails['creditCard']) {
                            foreach($creditCard as $key => $value) {
                                $message .= "<br> $key $value";
                            }
                        }
                    }

                    $data = [
                        'status' => 'success',
                        'payload' => [
                            'title' => $title,
                            'message' => $message,
                        ],
                    ];
        
                    $amount_s = number_format($payment->amount, 2);
                    $data['payload']['visa'] = $payment->lead->visa_id;
                    $data['payload']['title'] = "$$amount_s - $title";
                    $data['agent_id'] = $payment->agent_id;
                    $payment->responses()->create($data);
        
                    $misc = $payment->misc;
                    unset($misc['token']);
                    $misc['transId'] = $transId;
                    
                    $payment->update([
                        'status' => PaymentStatus::SUCCESSFUL,
                        'misc' => $misc,
                        'completed_at' => date('Y-m-d H:i:s'),
                    ]);
                    (new StripeController)->updateLead($payment);
        
                    // Remove the webhook
                    $baseUrl = config('authorize.baseUrl');
                    $merchantName  = config('authorize.merchantName');
                    $transactionKey  = config('authorize.transactionKey');
                    if($webhookId = $misc['webhook']) {
                        $response = Http::withBasicAuth($merchantName, $transactionKey)
                            ->delete("$baseUrl/rest/v1/webhooks/$webhookId");
                        if(!$response->successful()) \logger()->error($response->body());
                    } else {
                        \logger()->error($misc);
                    }
                } else {
                    $title = "Payment $transaction[transactionStatus]";
                    if($paymentDetails = $transaction['payment']) {
                        if($creditCard = $paymentDetails['creditCard']) {
                            foreach($creditCard as $key => $value) {
                                $message .= "<br> $key $value";
                            }
                        }
                    }
                    
                    $data = [
                        'status' => $transaction['responseCode'] == 4
                            ? 'secondary'
                            : 'error',
                        'payload' => [
                            'title' => $title,
                            'message' => $message,
                        ],
                    ];

                    $amount_s = number_format($payment->amount, 2);
                    $data['payload']['visa'] = $payment->lead->visa_id;
                    $data['payload']['title'] = "$$amount_s - $title";
                    $data['agent_id'] = $payment->agent_id;
                    $payment->responses()->create($data);

                    if($transaction['responseCode'] != 4) {
                        $payment->update(['status' => PaymentStatus::FAILED]);
                    }
                }
            } else {
                \logger($response->body());
            }
        }
    }
}
