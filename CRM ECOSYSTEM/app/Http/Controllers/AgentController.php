<?php

namespace App\Http\Controllers;

use App\Enums\AgentType;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Agent;
use App\Models\ConversionRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');

        $usersQ = User::has('agent')->with('agent.break');

        $count = $usersQ->count();
        $users = $usersQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('firstname', 'asc')->get();

        // processing data
        $array = [];
        foreach($users as $user) {
            if($break = $user->agent->break) {
                $break->seconds = strtotime('now') - strtotime($break->created_at);
            }

            $array[] = [
                'id' => $user->id,
                'type' => $user->agent->type->name,
                'break' => $break,
                'role' => $user->role ? $user->role->name : 'AGENT',
                'name' => "$user->firstname $user->lastname",
                'email' => $user->email,
            ];
        }

        return [
            'agents' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalAgents' => $count
        ];
    }

    public function show(Request $request, User $user)
    {
        if($user->agent->type == AgentType::FILE_OPENING) {
            $targets = $user->agent->targets;
            $targets = [
                'products' => $user->agent->type
                    ->products()
                    ->map(function($visa) use ($targets) {
                    return $targets && isset($targets['products']) && isset($targets['products'][$visa->id]) ? [
                        'id' => $visa->id,
                        'name' => $visa->name,
                        'commission' => $targets['products'][$visa->id],
                    ] : [
                        'id' => $visa->id,
                        'name' => $visa->name,
                        'commission' => 100,
                    ];
                }),
                'goal' => $user->agent->targets['goal'] ?? 10,
            ];
        } else {
            $targets = [
                'percentage' => $user->agent->targets['percentage'] ?? 10,
                'goal' => $user->agent->targets['goal'] ?? 20000,
            ];
        }
        return [
            'id' => $user->id,
            'type' => $user->agent->type->value,
            'passwordReset' => $user->agent->password_reset && isset($user->agent->password_reset['password']) ? 1 : 0,
            'role' => $user->role ? $user->role->value : UserRole::AGENT,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'targets' => $targets,
            'commission' => $this->commission($request, $user->agent)
        ];
    }

    public function commission(Request $request, Agent $agent = null)
    {
        $agent = $agent ?? $request->user()->agent;
        $fromDate = strtotime(date('Y-m-27 03:00:00'));
        if($fromDate > time()) {
            $date = date('Y-m', strtotime('-1 Month'));
            $fromDate = strtotime(date("$date-27 03:00:00"));
        }
        $fromDate = date('Y-m-d H:i:s', $fromDate);
        

        $conversion = ConversionRate::where('from', 'USD')->first();
        if($agent->type == AgentType::FILE_OPENING) {
            $commission = 0;
            
            $histories = $agent->history()
                ->where('created_at', '>=', $fromDate)
                ->get();

            foreach($histories as $history) {
                \Log::info($history->id);
                $commission += isset($agent->targets['products']) && isset($agent->targets['products'][$history->visa_id])
                    ? $agent->targets['products'][$history->visa_id]
                    : 100;
            }

            $progress = $histories->count();
            $goal = $agent->targets['goal'] ?? 10;
            return [
                'commission' => number_format($commission - ($agent->chargebacks * $conversion->rate), 2) . 'ZAR',
                'progress' => "$progress sales",
                'goal' => "$goal sales",
                'perc' => number_format($progress / $goal * 100, 1) . '%',
                'max' => $goal,
                'prog' => $progress,
            ];
        } else {
            $progress = $agent->payments()
                ->where('completed_at', '>=', $fromDate)
                ->where('status', PaymentStatus::SUCCESSFUL)
                ->sum('amount');
            $goal = $agent->targets['goal'] ?? 20000;
            $percentage = $agent->targets['percentage'] ?? 10;
    
            $commission = (($progress * ($percentage / 100)) - $agent->chargebacks) * $conversion->rate;

            return [
                'commission' => number_format($commission, 2) . 'ZAR',
                'progress' => '$' . number_format($progress, 2),
                'goal' => '$' . number_format($goal),
                'perc' => number_format($progress / $goal * 100, 1) . '%',
                'max' => $goal,
                'prog' => $progress,
            ];
        }
    }

    public function store(Request $request)
    {
        $user = User::create([
            'firstname' => $request->post('firstname'),
            'lastname' => $request->post('lastname'),
            'email' => $request->post('email'),
            'password' => bcrypt($request->post('password')),
            'role' => UserRole::from($request->post('role')),
        ]);

        Agent::create([
            'user_id' => $user->id,
            'type' => AgentType::from($request->post('type')),
            'targets' => [],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $user->agent->update([
            'type' => AgentType::from($request->post('type')),
        ]);

        $data = [
            'role' => UserRole::from($request->post('role')),
            'firstname' => $request->post('firstname'),
            'lastname' => $request->post('lastname'),
            'email' => $request->post('email'),
        ];

        if($user->id == 2 || $user->id == 1) { // super users
            unset($data['role']);
        }

        if($password = $request->post('password')) {
            $data['password'] = bcrypt($password);
        }
        $user->update($data);
    }

    public function updateGoal(Request $request, User $user)
    {
        $products = [];
        if($request->post('products')) {
            foreach($request->post('products') as $product) {
                $products[$product['id']] = $product['commission'];
            }
        }

        $user->agent->update([
            'targets' => [
                'percentage' => $request->post('percentage'),
                'goal' => $request->post('goal'),
                'products' => $products,
            ]
        ]);
    }

    public function destroy(User $user)
    {
        if($user->id == 2 || $user->id == 1) { // super users
            return response()->json([], Response::HTTP_FORBIDDEN);
        }
        $user->delete();
    }

    public function resetPassword(Request $request) {
        $agent = $request->user()->agent;
        return $agent->password_reset ?? 0;
    }

    public function updatePassword(Request $request) {
        $agent = $request->user()->agent;
        $agent->password_reset = [
            'password' => Hash::make($request->post('password')),
        ];
        $agent->save();
    }
}
