<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Update presence after Laravel has sent the response. Presence is useful
     * for chat, but it should never delay a successful login or dashboard load.
     */
    public function terminate(Request $request, Response $response): void
    {
        $user = $request->user();

        if ($user && (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinute()))) {
            User::query()
                ->whereKey($user->id)
                ->update(['last_seen_at' => now()]);
        }
    }
}
