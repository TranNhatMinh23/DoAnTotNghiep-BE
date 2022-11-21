<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdminOrManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user() && ( Auth::user()->role_id == User::ROLE_ADMIN || Auth::user()->role_id == User::ROLE_COMPANY_ADMIN ) && Auth::user()->active_status == User::ACTIVE_USER) {
            return $next($request);
        }
        if(Auth::user()->active_status == User::BLOCK_USER){
            return response()->json(['error' => "Your account is currently locked!"], 401);
        }
        return response()->json(['error' => "You do not have an admin role or a managerial role of the company!"], 403);
    }
}
