<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\SquarePaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SquareController extends Controller
{
    // Testing cards: https://developer.squareup.com/docs/devtools/sandbox/payments
    // Checkout: https://developer.squareup.com/explorer/square/checkout-api/create-payment-link
    // All events: https://developer.squareup.com/docs/webhooks/v2webhook-events-tech-ref
    // Payment updated: https://developer.squareup.com/reference/square/payments-api/webhooks/payment.updated

    public function webhook(Request $request) {
        \Log::info('webhook: ' . json_encode($request->all()));
        $type = $request->post('type');
        if($type == 'payment.updated') {
            $paymentResponse = $request->post('data')['object']['payment'];
            $orderId = $paymentResponse['order_id'];
            $payment = Payment::where('misc->order_id', $orderId)->firstOrFail();
            $status = SquarePaymentStatus::fromName($paymentResponse['status']);

            if($status == SquarePaymentStatus::COMPLETED) {
                $title = 'Payment successful';
                $cardDetails = $paymentResponse['card_details'] ?? [];
                $card = $cardDetails['card'] ?? [
                    'card_brand' => '',
                    'card_type' => '',
                    'last_4' => '',
                    'exp_month' => '',
                    'exp_year' => '',
                    'bin' => '',
                ];
                $data = [
                    'status' => 'success',
                    'payload' => [
                        'title' => $title,
                        'message' => $cardDetails['statement_description'] ?? ''
                            . "<br>$card[card_brand] $card[card_type] ***$card[last_4]"
                            . "<br>Exp $card[exp_month]/$card[exp_year]"
                            . "<br>Bin $card[bin]",
                    ],
                ];

                $amount_s = number_format($payment->amount, 2);
                $data['payload']['visa'] = $payment->lead->visa_id;
                $data['payload']['title'] = "$$amount_s - $title";
                $data['agent_id'] = $payment->agent_id;
                $payment->responses()->create($data);

                $misc = $payment->misc;
                $misc['receipt_url'] = $paymentResponse['receipt_url'];
                $payment->update([
                    'status' => PaymentStatus::SUCCESSFUL,
                    'misc' => $misc,
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);
                (new StripeController)->updateLead($payment);
            } else {
                $title = "Payment $paymentResponse[status]";
                $cardDetails = $paymentResponse['card_details'] ?? [];
                $card = $cardDetails['card'] ?? [];
                $data = [
                    'status' => in_array($status, [SquarePaymentStatus::FAILED, SquarePaymentStatus::CANCELED])
                        ? 'error'
                        : 'secondary',
                    'payload' => [
                        'title' => $title,
                        'message' => $paymentResponse['statement_description'] ?? ''
                            . "<br>$card[card_brand] $card[card_type] ***$card[last_4]"
                            . "<br>Exp $card[exp_month]/$card[exp_year]"
                            . "<br>Bin $card[bin]"
                            . '<br>CVV status ' . ($cardDetails['cvv_status'] ?? 'none')
                            . '<br>AVS status ' . ($cardDetails['avs_status'] ?? 'none'),
                    ],
                ];

                foreach($cardDetails['errors'] ?? [] as $error) {
                    $data['payload']['message'] .= "<br>$error[detail]";
                }

                $amount_s = number_format($payment->amount, 2);
                $data['payload']['visa'] = $payment->lead->visa_id;
                $data['payload']['title'] = $payment->misc['method'] . " - $$amount_s - $title";
                $data['agent_id'] = $payment->agent_id;
                $payment->responses()->create($data);

                if(in_array($status, [SquarePaymentStatus::CANCELED, SquarePaymentStatus::FAILED]))
                    $payment->update(['status' => PaymentStatus::FAILED]);
            }
            return;
        }

        if(App::isProduction())
            throw new BadRequestHttpException();
        else \Log::error('Payment not found.');
        // Statuses
        // APPROVED, PENDING, COMPLETED, CANCELED, or FAILED
        // - card_details
        // - avs_status = Address Verification System (AVS_ACCEPTED, AVS_REJECTED, or AVS_NOT_CHECKED)
        // - statement_description
        // - receipt_url
    }

    public function return(Request $request, Payment $payment) {
        \Log::info('return: ' . json_encode($request->all()));
        $url = $payment->misc['receipt_url'] ?? config('app.customer_url') . '/customer/login';
        return redirect($url);
    }
}