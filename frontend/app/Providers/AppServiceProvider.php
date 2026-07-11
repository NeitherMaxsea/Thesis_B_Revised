<?php

namespace App\Providers;

use App\Models\JobApplication;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layout.dashboard', 'layout.employer'], function ($view) {
            /** @var User|null $user */
            $user = Auth::user();
            $recentJobApplications = collect();
            $unreadMessageCount = 0;

            if ($user instanceof User) {
                $unreadMessageCount = app(ChatService::class)->unreadCount($user);

                if ($user->account_type === 'employer') {
                    $recentJobApplications = JobApplication::query()
                        ->whereHas('job', fn ($query) => $query->where('user_id', $user->id))
                        ->with([
                            'applicant:id,name,first_name,last_name,account_type',
                            'job:id,user_id,title',
                            'conversation:id,job_application_id',
                        ])
                        ->latest()
                        ->limit(5)
                        ->get();
                }
            }

            $view->with([
                'unreadMessageCount' => $unreadMessageCount,
                'recentJobApplications' => $recentJobApplications,
            ]);
        });
    }
}
