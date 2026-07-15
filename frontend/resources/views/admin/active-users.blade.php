@extends('layout.admin')

@php
    use Illuminate\Support\Str;

    $activeUserName = static function ($user): string {
        $profileName = trim(implode(' ', array_filter([$user->first_name, $user->last_name])));

        if ($user->account_type === 'employer' && filled($user->company_name)) {
            return trim((string) $user->company_name);
        }

        return $profileName !== '' ? $profileName : $user->name;
    };
@endphp

@section('breadcrumb')
    <i data-lucide="house"></i>
    <i data-lucide="chevron-right"></i>
    <strong>Active Users</strong>
@endsection

@section('content')
<section class="admin-active-users-page" aria-labelledby="active-users-title">
    <header class="admin-active-users-page__header">
        <div>
            <p>Live availability</p>
            <h1 id="active-users-title">Active users</h1>
            <span>People using the platform in the last two minutes are available for a secure chat.</span>
        </div>
        <strong class="admin-active-users-page__count"><i data-lucide="radio-tower"></i>{{ $onlineUsers->count() }} online now</strong>
    </header>

    <div class="admin-active-users-grid" data-admin-active-users-grid>
        @forelse ($onlineUsers as $onlineUser)
            @php
                $displayName = $activeUserName($onlineUser);
                $initials = Str::of($displayName)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AU';
            @endphp
            <article class="admin-active-user-card">
                <span class="admin-active-user-card__avatar" aria-hidden="true">{{ $initials }}</span>
                <div class="admin-active-user-card__copy">
                    <span><strong>{{ $displayName }}</strong><em><i></i> Online now</em></span>
                    <small>{{ $onlineUser->account_type === 'employer' ? 'Employer' : 'PWD Applicant' }}</small>
                    <p>{{ $onlineUser->email }}</p>
                </div>
                <form action="{{ route('messages.conversations.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $onlineUser->id }}">
                    <button type="submit"><i data-lucide="messages-square"></i> Message</button>
                </form>
            </article>
        @empty
            <div class="admin-active-users-empty">
                <span><i data-lucide="users-round"></i></span>
                <h2>No active contacts yet</h2>
                <p>Users will appear here as soon as they are active on the platform.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
