<?php

namespace App\Http\Controllers;

use App\Models\AgentBreak;
use Illuminate\Http\Request;

class AgentBreakController extends Controller
{
    public function index(Request $request) {
        $date = $request->post('date')
            ? $request->post('date')
            : date('Y-m-d');

        $breaks = AgentBreak::where('created_at', '>=', "$date 06:00:00")
            ->where('created_at', '<=', "$date 23:59:59")
            ->with('agent.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return $breaks->map(function($break) {
            $overtime = false;
            if($break->active) {
                $diff = strtotime('now') - strtotime($break->created_at);
                if(
                    ($break->type == 'cigarettes' && $diff > 60 * 10) ||
                    ($break->type == 'lunch' && $diff > 60 * 30) ||
                    ($break->type == 'toilet' && $diff > 60 * 5)
                ) {
                    $overtime = true;
                }
            } else {
                $duration = strtotime($break->updated_at) - strtotime($break->created_at);
                if(
                    ($break->type == 'cigarettes' && $duration > 60 * 10) ||
                    ($break->type == 'lunch' && $duration > 60 * 30) ||
                    ($break->type == 'toilet' && $duration > 60 * 5)
                ) {
                    $overtime = true;
                }
            }
            return [
                'name' => $break->agent->user->firstname . ' ' . $break->agent->user->lastname,
                'time' => date('H:i:s', strtotime($break->created_at)),
                'type' => $break->type,
                'overtime' => $overtime,
                'seconds' => $diff ?? null,
                'duration' => gmdate('H:i:s', strtotime($break->updated_at) - strtotime($break->created_at)),
            ];
        });
    }

    public function available(Request $request) {
        $user = $request->user();
        $agent = $user->agent;

        if($break = $agent->break) {
            $break->seconds = strtotime('now') - strtotime($break->created_at);
            return $break;
        }

        $breaks = [
            'Cigarettes', // 10 min
            'Lunch', // 30 min
            'Toilet', // 5 min
            'Meeting',
            'Training',
        ];
        return $breaks;
    }

    public function start(Request $request, $type) {
        $user = $request->user();
        $agent = $user->agent;

        $break = $agent->breaks()->create([
            'type' => strtolower($type),
            'seconds' => 0,
        ]);
        return $break;
    }

    public function stop(Request $request) {
        $user = $request->user();
        $agent = $user->agent;

        $agent->breaks()->where('active', true)->update([
            'active' => false,
        ]);

        return $this->available($request);
    }
}
