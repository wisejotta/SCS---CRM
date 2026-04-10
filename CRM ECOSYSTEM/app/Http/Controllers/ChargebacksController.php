<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Chargeback;
use Illuminate\Http\Request;

class ChargebacksController extends Controller
{
    function index() {
        $chargebacks = Chargeback::with('agent.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return $chargebacks->map(function($chargeback) {
            return [
                'id' => $chargeback->id,
                'agent' => $chargeback->agent->user->firstname.' '.$chargeback->agent->user->lastname,
                'amount' => '$' . number_format($chargeback->amount, 2),
                'created_at' => date('M d, Y H:i', strtotime($chargeback->created_at)),
            ];
        });
    }

    function userChargebacks(Request $request) {
        $user = $request->user();
        $agent = $user->agent;

        $chargebacks = $agent->myChargebacks()
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->orderBy('created_at', 'desc')
            ->get();

        return $chargebacks->map(function($chargeback) {
            return [
                'id' => $chargeback->id,
                'amount' => '$' . number_format($chargeback->amount, 2),
                'created_at' => date('M d, Y H:i', strtotime($chargeback->created_at)),
            ];
        });
    }

    function store(Request $request) {
        $agent = Agent::where('id', $request->agent)->firstOrFail();
        $agent->myChargebacks()->create([
            'amount' => $request->amount,
        ]);

        $agent->chargebacks += $request->amount;
        $agent->save();
    }
}
