<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user?->account_type === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($user?->account_type === 'pwd_applicant') {
                    return redirect()->route(
                        $user->applicant_review_status === 'approved'
                            ? 'applicant.dashboard'
                            : 'applicant.review'
                    );
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
