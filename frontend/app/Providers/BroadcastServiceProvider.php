<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Channel authorization performs the authenticated-user check and
        // returns 403 for guests instead of redirecting an Echo request.
        Broadcast::routes(['middleware' => ['web']]);

        require base_path('routes/channels.php');
    }
}
