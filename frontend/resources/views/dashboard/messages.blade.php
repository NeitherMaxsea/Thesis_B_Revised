@extends('layout.dashboard')

@php
    $messageCount = $selectedConversation?->messages->count() ?? 0;
    $hasContacts = $contacts->isNotEmpty();
    $isEmployer = $user->account_type === 'employer';
    $messagingDescription = $isEmployer
        ? 'Message applicants after they apply, or contact platform support securely.'
        : 'Message platform support and employers after you apply for a job.';
@endphp

@section('content')
<section
    class="applicant-chat"
    data-applicant-chat
    data-current-user-id="{{ $user->id }}"
    @if ($selectedConversation)
        data-conversation-id="{{ $selectedConversation->id }}"
        data-send-url="{{ route('messages.store', $selectedConversation) }}"
    @endif
    aria-labelledby="applicant-chat-title"
>
    <div class="applicant-chat__topbar">
        <div>
            <p class="applicant-chat__eyebrow">Secure messaging</p>
            <h1 id="applicant-chat-title">Messages</h1>
            <p>{{ $messagingDescription }}</p>
        </div>
        <span class="applicant-chat__live" data-chat-live-status role="status">
            <span aria-hidden="true"></span>
            Connecting to live chat…
        </span>
    </div>

    <div class="applicant-chat__shell">
        <aside class="applicant-chat__sidebar" aria-label="Your conversations">
            <label class="applicant-chat__search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></svg>
                <span class="sr-only">Search conversations</span>
                <input type="search" data-chat-search placeholder="Search conversations..." autocomplete="off">
            </label>

            <div class="applicant-chat__list" data-chat-conversation-list>
                @if ($conversations->isNotEmpty())
                    <p class="applicant-chat__list-label">Conversations</p>
                    @foreach ($conversations as $conversation)
                        @php
                            $contact = $conversation->otherParticipant($user);
                            $lastMessage = $conversation->latestMessage;
                            $isActive = $selectedConversation?->is($conversation);
                        @endphp
                        @if ($contact)
                            <a
                                href="{{ route('messages.index', ['conversation' => $conversation->id]) }}"
                                class="applicant-chat__contact {{ $isActive ? 'is-active' : '' }}"
                                data-chat-contact
                                data-chat-search-text="{{ strtolower($chatService->displayName($contact).' '.($lastMessage?->body ?? '')) }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                <span class="applicant-chat__avatar" aria-hidden="true">{{ $chatService->initials($contact) }}</span>
                                <span class="applicant-chat__contact-copy">
                                    <strong>{{ $chatService->displayName($contact) }}</strong>
                                    <small>{{ $contact->account_type === 'admin' ? 'Platform Support' : ($contact->account_type === 'employer' ? 'Employer' : 'PWD Applicant') }}</small>
                                    <em>{{ $lastMessage?->body ?: 'Start a secure conversation' }}</em>
                                </span>
                                @if ($lastMessage?->created_at)
                                    <time datetime="{{ $lastMessage->created_at->toIso8601String() }}">{{ $lastMessage->created_at->isToday() ? $lastMessage->created_at->format('g:i A') : $lastMessage->created_at->format('M j') }}</time>
                                @endif
                            </a>
                        @endif
                    @endforeach
                @endif

                @if ($hasContacts)
                    <p class="applicant-chat__list-label">Start a conversation</p>
                    @foreach ($contacts as $contact)
                        @php
                            $alreadyOpen = $conversations->contains(fn ($conversation) => $conversation->hasParticipant($contact));
                        @endphp
                        @unless ($alreadyOpen)
                            <a
                                href="{{ route('messages.index', ['recipient' => $contact->id]) }}"
                                class="applicant-chat__contact applicant-chat__contact--new"
                                data-chat-contact
                                data-chat-search-text="{{ strtolower($chatService->displayName($contact).' support') }}"
                            >
                                <span class="applicant-chat__avatar" aria-hidden="true">{{ $chatService->initials($contact) }}</span>
                                <span class="applicant-chat__contact-copy">
                                    <strong>{{ $chatService->displayName($contact) }}</strong>
                                    <small>{{ $contact->account_type === 'admin' ? 'Platform Support' : ($contact->account_type === 'employer' ? 'Employer' : 'PWD Applicant') }}</small>
                                    <em>Start a secure conversation</em>
                                </span>
                                <svg class="applicant-chat__start-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                            </a>
                        @endunless
                    @endforeach
                @endif

                @unless ($conversations->isNotEmpty() || $hasContacts)
                    <div class="applicant-chat__no-contacts">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.5a3.5 3.5 0 0 1-3.5 3.5H8l-4 2v-12A3.5 3.5 0 0 1 7.5 5.5h9A3.5 3.5 0 0 1 20 9v6.5Z" /><path d="M8 11h8M8 14h5" /></svg>
                        <strong>No messages yet</strong>
                        <p>{{ $isEmployer ? 'New applicant conversations will appear here after someone applies to one of your jobs.' : 'Your employer conversation will appear here immediately after you apply for a job.' }}</p>
                    </div>
                @endunless
            </div>
        </aside>

        <article class="applicant-chat__conversation">
            @if ($selectedConversation && $selectedContact)
                <header class="applicant-chat__conversation-header">
                    <span class="applicant-chat__avatar applicant-chat__avatar--header" aria-hidden="true">{{ $chatService->initials($selectedContact) }}</span>
                    <span>
                        <strong>{{ $chatService->displayName($selectedContact) }}</strong>
                        <small>{{ $selectedContact->account_type === 'admin' ? 'Platform Support' : ($selectedContact->account_type === 'employer' ? 'Employer' : 'PWD Applicant') }} · Secure direct conversation</small>
                    </span>
                    <span class="applicant-chat__secure-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                        Private
                    </span>
                </header>

                <div class="applicant-chat__messages" data-chat-messages aria-live="polite" aria-label="Conversation messages">
                    @if ($messageCount === 0)
                        <div class="applicant-chat__conversation-empty" data-chat-empty>
                            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 15.5a3.5 3.5 0 0 1-3.5 3.5H8l-4 2v-12A3.5 3.5 0 0 1 7.5 5.5h9A3.5 3.5 0 0 1 20 9v6.5Z" /><path d="M8 11h8M8 14h5" /></svg></span>
                            <h2>Start the conversation</h2>
                            <p>Send a secure message to continue this conversation.</p>
                        </div>
                    @else
                        @foreach ($selectedConversation->messages as $message)
                            @php($isMine = $message->sender_id === $user->id)
                            <div class="applicant-chat__message {{ $isMine ? 'is-mine' : '' }}" data-message-id="{{ $message->id }}">
                                @unless ($isMine)
                                    <span class="applicant-chat__message-avatar" aria-hidden="true">{{ $chatService->initials($message->sender) }}</span>
                                @endunless
                                <div>
                                    @unless ($isMine)
                                        <strong>{{ $chatService->displayName($message->sender) }}</strong>
                                    @endunless
                                    <p>{{ $message->body }}</p>
                                    <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('g:i A') }}</time>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                @if ($canSend)
                    <form class="applicant-chat__composer" data-chat-send-form novalidate>
                        @csrf
                        <label class="sr-only" for="chat-message-body">Message {{ $chatService->displayName($selectedContact) }}</label>
                        <textarea id="chat-message-body" name="body" rows="1" maxlength="2000" data-chat-message-input placeholder="Write a message..." required></textarea>
                        <button type="submit" data-chat-send-button aria-label="Send message">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-8.5 18-3.4-7.1L2 10.5 21 3Z" /><path d="m9.1 13.9 4.3-4.3" /></svg>
                            <span>Send</span>
                        </button>
                        <p class="applicant-chat__composer-status" data-chat-composer-status role="status"></p>
                    </form>
                @else
                    <div class="applicant-chat__composer applicant-chat__composer--paused" role="status">
                        Messaging is temporarily unavailable for this conversation. Existing messages remain private and visible here.
                    </div>
                @endif
            @else
                <div class="applicant-chat__select-state">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 15.5a3.5 3.5 0 0 1-3.5 3.5H8l-4 2v-12A3.5 3.5 0 0 1 7.5 5.5h9A3.5 3.5 0 0 1 20 9v6.5Z" /><path d="M8 11h8M8 14h5" /></svg></span>
                    <p class="applicant-chat__eyebrow">Secure messaging</p>
                    <h2>Select a conversation</h2>
                    <p>{{ $hasContacts ? 'Choose a conversation from the list to view messages and reply.' : $messagingDescription }}</p>
                </div>
            @endif
        </article>
    </div>
</section>
@endsection
