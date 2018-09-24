<?php

namespace App\Http\Middleware;

use Closure;

use App\User;
use Illuminate\Http\Request;

class Admin
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
        $user = $request->user();

        // пользователь залогинен и подтвердил email
            if ($user && $user->role->name === 'Админ') {
                return $next($request);
            }
            elseif ($user) {
                return redirect('/noAccess');
            }
            else
                return redirect('/login');
    }
}
