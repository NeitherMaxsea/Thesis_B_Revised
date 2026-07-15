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

        // The admin header is present on every administration screen, so keep
        // its chat badge and the small online roster in one shared composer.
        // A user is considered online while their activity heartbeat is less
        // than two minutes old (the same rule used by the chat presence UI).
        View::composer('layout.admin', function ($view) {
            /** @var User|null $user */
            $user = Auth::user();
            $onlineUsers = collect();
            $onlineUserCount = 0;
            $unreadMessageCount = 0;
            $inboxMessageCursor = 0;

            if ($user instanceof User && $user->account_type === 'admin') {
                $onlineQuery = User::query()
                    ->where('last_seen_at', '>=', now()->subMinutes(2))
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('account_type', 'pwd_applicant')
                                ->where('applicant_review_status', 'approved');
                        })->orWhere(function ($query) {
                            $query->where('account_type', 'employer')
                                ->where('employer_document_status', 'valid');
                        });
                    });

                $onlineUserCount = (clone $onlineQuery)->count();
                $onlineUsers = $onlineQuery
                    ->orderByDesc('last_seen_at')
                    ->limit(6)
                    ->get([
                        'id',
                        'name',
                        'first_name',
                        'last_name',
                        'company_name',
                        'account_type',
                        'last_seen_at',
                    ]);
                $chatService = app(ChatService::class);
                $unreadMessageCount = $chatService->totalUnreadCount($user);
                $inboxMessageCursor = $chatService->inboxMessageCursor($user);
            }

            $view->with([
                'onlineUsers' => $onlineUsers,
                'onlineUserCount' => $onlineUserCount,
                'unreadMessageCount' => $unreadMessageCount,
                'inboxMessageCursor' => $inboxMessageCursor,
            ]);
        });
    }
}
