<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request, $type)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('search');
        $dateRange = $request->post('dateRange');

        $paymentsQ = Payment::where('status', PaymentStatus::SUCCESSFUL)
            ->where('lead_status',  LeadStatus::fromName(strtoupper($type)));

        if($search) {
            $paymentsQ = $paymentsQ
                ->whereHas('lead', function($query) use($search) {
                    $query
                        ->whereHas('customer', function ($query) use($search) {
                            return $query->where('phone_number', 'LIKE', "%$search%")
                                ->orWhere('country', 'LIKE', "%$search%")
                                ->orWhereHas('user', function($query) use($search) {
                                    $query->where('firstname', 'LIKE', "%$search%")
                                        ->orWhere('lastname', 'LIKE', "%$search%")
                                        ->orWhere('email', 'LIKE', "%$search%");
                                })->orWhereHas('lead', function($query) use($search) {
                                    $query->where('id', $search);
                                });
                        });
                });
        }

        if($dateRange) {
            $dates = explode(' to ', $dateRange);
            if(count($dates) == 2) {
                $from = date('Y-m-d 00:00:00', strtotime($dates[0]));
                $to = date('Y-m-d 23:59:59', strtotime($dates[1]));
                $paymentsQ->where('completed_at', '>=', $from)->where('completed_at', '<=', $to);
            }
        }

        $paymentsQ = $paymentsQ->with('agent.user')->with('lead.customer.user');
        $count = $paymentsQ->count();
        $payments = $paymentsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('completed_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($payments as $payment) {
            $array[] = [
                'id' => $payment->id,
                'file_number' => $payment->lead->id,
                'customer' => $payment->lead->customer->user->firstname . ' ' . $payment->lead->customer->user->lastname,
                'amount' => '$' . number_format($payment->amount, 2),
                'date' => date('M d, Y, h:i A', strtotime($payment->completed_at)),
                'agent' => $payment->agent
                    ? $payment->agent->user->firstname . ' ' . $payment->agent->user->lastname
                    : '-'
            ];
        }

        return [
            'payments' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalAgents' => $count
        ];
    }
    
    public function agentPayments(Request $request, User $user)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('search');
        $dateRange = $request->post('dateRange');

        $paymentsQ = $user->agent->payments()->where('status', PaymentStatus::SUCCESSFUL);
        if($search) {
            $paymentsQ = $paymentsQ->whereHas('lead', function($query) use($search) {
                $query
                    ->whereHas('customer', function ($query) use($search) {
                        return $query->where('phone_number', 'LIKE', "%$search%")
                            ->orWhere('country', 'LIKE', "%$search%")
                            ->orWhereHas('user', function($query) use($search) {
                                $query->where('firstname', 'LIKE', "%$search%")
                                    ->orWhere('lastname', 'LIKE', "%$search%")
                                    ->orWhere('email', 'LIKE', "%$search%");
                            })->orWhereHas('lead', function($query) use($search) {
                                $query->where('id', $search);
                            });
                    });
            });
        }

        if($dateRange) {
            $dates = explode(' to ', $dateRange);
            if(count($dates) == 2) {
                $from = date('Y-m-d 00:00:00', strtotime($dates[0]));
                $to = date('Y-m-d 23:59:59', strtotime($dates[1]));
                $paymentsQ->where('completed_at', '>=', $from)->where('completed_at', '<=', $to);
            }
        }

        $paymentsQ = $paymentsQ->with('agent.user')->with('lead.customer.user');
        $count = $paymentsQ->count();
        $payments = $paymentsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('completed_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($payments as $payment) {
            $array[] = [
                'id' => $payment->id,
                'file_number' => $payment->lead->id,
                'customer' => $payment->lead->customer->user->firstname . ' ' . $payment->lead->customer->user->lastname,
                'amount' => '$' . number_format($payment->amount, 2),
                'date' => date('M d, Y, h:i A', strtotime($payment->completed_at)),
                'agent' => $payment->agent
                    ? $payment->agent->user->firstname . ' ' . $payment->agent->user->lastname
                    : '-'
            ];
        }

        return [
            'payments' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalAgents' => $count
        ];
    }

    public function opportunities(Request $request)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('search');

        $user = $request->user();

        $paymentsQ = $user->agent->payments()->where('status', PaymentStatus::SUCCESSFUL)
            ->has('lead')
            ->with('lead.customer.user');

            if($search) {
                $paymentsQ->whereHas('lead', function($query) use($search) {
                    $query->whereHas('customer', function ($query) use($search) {
                        return $query->where('phone_number', 'LIKE', "%$search%")
                            ->orWhere('country', 'LIKE', "%$search%")
                            ->orWhereHas('user', function($query) use($search) {
                                $query->where('firstname', 'LIKE', "%$search%")
                                    ->orWhere('lastname', 'LIKE', "%$search%")
                                    ->orWhere('email', 'LIKE', "%$search%");
                            })->orWhereHas('lead', function($query) use($search) {
                                $query->where('id', $search);
                            });
                    });
                });
            }
            
        $count = $paymentsQ->count();
        $payments = $paymentsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('completed_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($payments as $payment) {
            $array[] = [
                'id' => $payment->id,
                'file_number' => $payment->lead->id,
                'customer' => $payment->lead->customer->user->firstname . ' ' . $payment->lead->customer->user->lastname,
                'amount' => '$' . number_format($payment->amount, 2),
                'date' => date('M d, Y, h:i A', strtotime($payment->completed_at))
            ];
        }

        return [
            'payments' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalAgents' => $count
        ];
    }
}
