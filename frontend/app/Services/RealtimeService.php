<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keeps a temporary WebSocket outage from turning a completed user action
 * into a failed HTTP request. Events still broadcast immediately whenever
 * the configured broadcaster is available.
 */
class RealtimeService
{
    public function broadcast(object $event): void
    {
        try {
            event($event);
        } catch (Throwable $exception) {
            // A persisted message, profile update, application, or job post must
            // remain successful even when Reverb/the provider is restarting.
            try {
                Log::warning('Real-time delivery was unavailable; the request completed without a live update.', [
                    'event' => $event::class,
                    'exception' => $exception->getMessage(),
                ]);
            } catch (Throwable) {
                // Logging must never make the original request fail either.
            }
        }
    }
}
