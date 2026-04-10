<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login()
    {
        if(Auth::user()) {
            return redirect(RouteServiceProvider::HOME);
        }
        return view('application');
    }

    public function logout($type = null)
    {
        return redirect(
            $type == 'customer' ? 'customer/login' : '/'
        )->with(Auth::logout());
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
 
        if(!Auth::attempt($credentials, $request->post('remember'))) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::find(Auth::id());
        if($user->role != UserRole::CUSTOMER && $user->customer) {
            $user->role = UserRole::CUSTOMER;
            $user->save();
        }

        return [
            'id' => $user->id,
            'name' => $user->firstname,
            'surname' => $user->lastname,
            'email' => $user->email,
            'type' => $user->agent ? $user->agent->type->name : null,
            'role' => $user->role == UserRole::ADMIN || $user->role == UserRole::CUSTOMER
                ? $user->role->name
                : $user->agent->type->name,
        ];
    }

    public function verify(Request $request)
    {
        $user = $request->user();
        if(!$user) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return [
            'id' => $user->id,
            'name' => $user->firstname,
            'surname' => $user->lastname,
            'email' => $user->email,
            'access' => $user->role->access(),
            'admin' => $user->role == UserRole::ADMIN,
        ];
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if($user && $user->role == UserRole::ADMIN) {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required',
                'role' => [
                    'required',
                    'integer',
                    Rule::in(array_column(UserRole::cases(), 'value')),
                ],
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required',
            ]);
    
            if (!$validator->fails()) {
                $fields = $request->only([
                    'firstname',
                    'lastname',
                    'role',
                    'email',
                    'password',
                ]);
                
                $fields['password'] = bcrypt($fields['password']);
                $user = User::create($fields);
                Agent::create(['user_id' => $user->id]);
                return ['message' => 'User added successfuly.'];
            } else {
                return response()->json([
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return response()->json([
                'message' => 'Not allowed.'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
