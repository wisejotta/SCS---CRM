<?php

namespace App\Http\Middleware;

use App\Enums\AgentType;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class AgentAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if($user->role == UserRole::CUSTOMER) {
            return redirect('/customer');
        }
        return $next($request);
    }
}
