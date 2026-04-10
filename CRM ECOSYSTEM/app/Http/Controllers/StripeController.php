<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StripeController extends Controller
{
    public function return(Request $request)
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, config('stripe.endpoint_secret')
            );
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            echo '⚠️  Webhook error while validating signature.';
            http_response_code(400);
            exit();
        }

        $stripe = new \Stripe\StripeClient(config('stripe.secret'));

        \Log::info($event->type . ': '. json_encode($request->all()));
        switch($event->type) {
            case 'payment_intent.created':
                $payments = Payment::whereNotNull('misc')
                    ->whereNull('misc->pi_id')
                    ->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-24 hours')))
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach($payments as $p) {
                    $misc = $p->misc;
                    if(isset($misc['cs_id'])) {
                        $checkout = $stripe->checkout->sessions->retrieve($misc['cs_id'], []);
                    
                        if($checkout->payment_intent == $event->data->object->id) {
                            $misc['pi_id'] = $checkout->payment_intent;
                            $p->update(['misc' => $misc]);
                            
                            PaymentResponse::where('payload->pi_id', $checkout->payment_intent)->update([
                                'payment_id' => $p->id,
                                'agent_id' => $p->lead_id ? $p->lead->agent_id : null
                            ]);
    
                            if($res = $p->responses()->where('status', 'success')->first()) {
                                $misc['receipt_url'] = $res['payload']['receipt_url'];
                                $p->update([
                                    'status' => PaymentStatus::SUCCESSFUL,
                                    'misc' => $misc,
                                    'completed_at' => date('Y-m-d H:i:s'),
                                ]);
                                $this->updateLead($p);
                            }
                            break;
                        }
                    }
                }
                break;
        
            case 'charge.succeeded':
                $title = 'Payment successful';
                $data = [
                    'status' => 'success',
                    'payload' => [
                        'title' => $title,
                        'message' => $event->data->object->outcome->seller_message
                    ],
                ];

                $payment = Payment::where('misc->pi_id', $event->data->object->payment_intent)->first();
                if($payment) {
                    $amount_s = number_format($payment->amount, 2);
                    $data['payload']['visa'] = $payment->lead->visa_id;
                    $data['payload']['title'] = "$$amount_s - $title";
                    $data['agent_id'] = $payment->agent_id;
                    $payment->responses()->create($data);

                    $misc = $payment->misc;
                    $misc['receipt_url'] = $event->data->object->receipt_url;
                    $payment->update([
                        'status' => PaymentStatus::SUCCESSFUL,
                        'misc' => $misc,
                        'completed_at' => date('Y-m-d H:i:s'),
                    ]);
                    $this->updateLead($payment);
                } else {
                    $amount_s = number_format($event->data->object->amount/100, 2);
                    $data['payload']['title'] = "$$amount_s - $title";
                    $data['payload']['pi_id'] = $event->data->object->payment_intent;
                    $data['payload']['receipt_url'] = $event->data->object->receipt_url;
                    PaymentResponse::create($data);
                }
                break;

            case 'charge.failed':
                $payment = Payment::where('misc->pi_id', $event->data->object->payment_intent)->first();
                $details = $event->data->object->payment_method_details->card;
                $msg = $event->data->object->failure_message;
                $title = "$msg - $details->network ***$details->last4";

                $data = [
                    'status' => 'error',
                    'payload' => [
                        'title' => $title,
                        'message' => $event->data->object->outcome->seller_message,
                        'csv' => $event->data->object->payment_method_details->card->checks->cvc_check
                    ],
                ];

                if($payment) {
                    $amount_s = number_format($payment->amount, 2);
                    $data['payload']['visa'] = $payment->lead->visa_id;
                    $data['payload']['title'] = $payment->misc['method'] . " - $$amount_s - $title";
                    $data['agent_id'] = $payment->agent_id;
                    $payment->responses()->create($data);
                    $payment->update(['status' => PaymentStatus::FAILED]);
                } else {
                    $amount_s = number_format($event->data->object->amount/100, 2);
                    $data['payload']['title'] = "$$amount_s - $title";
                    $data['payload']['pi_id'] = $event->data->object->payment_intent;
                    PaymentResponse::create($data);
                }
                break;
        }
    }

    public function updateLead($payment) {
        $lead = $payment->lead;
        $paid = $lead->payments()
            ->where('complete', false)
            ->where('status', PaymentStatus::SUCCESSFUL)
            ->sum('amount');

        if($paid >= $lead->visa->price) {
            $lead->payments()->update([
                'complete' => true
            ]);
            $lead->history()->create([
                'status' => $lead->status,
                'visa_id' => $lead->visa_id,
                'agent_id' => $lead->agent_id,
            ]);

            if($lead->status == LeadStatus::FILE_OPENING) {
                $lead->update([
                    'status' => LeadStatus::UPGRADE,
                    'assigned_at' => null,
                    'reassigned_at' => null,
                    'unassigned_at' => null,
                    'sort_date' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $lead->update([
                    'sort_date' => date('Y-m-d H:i:s'),
                ]);
            }

            if($lead->mailchimp_id) { // remove from collection
                $baseUrl = config('mailchimp.baseUrl');
                $apiKey = config('mailchimp.apiKey');
                $listId = config('mailchimp.listId');
                Http::withBasicAuth('key', $apiKey)
                    ->delete("$baseUrl/lists/$listId/members/$lead->mailchimp_id");
            }
        } else { // collection
            (new LeadController)->addToMailchimp($lead, 'Collection', '');
        }
    }

    public function success(Request $request, Payment $payment)
    {
        $url = $payment->misc['receipt_url'] ?? config('app.customer_url') . '/customer/login';
        return redirect($url);
    }
}
