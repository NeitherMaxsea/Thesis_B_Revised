@extends('layout.dashboard')

@php
    $messageCount = $selectedConversation?->messages->count() ?? 0;
    $hasContacts = $contacts->isNotEmpty();
    $isEmployer = $user->account_type === 'employer';
    $messagingDescription = $isEmployer
        ? 'Message applicants after they apply, or contact platform support securely.'
        : 'Message platform support and employers after you apply for a job.';
    $selectedSettings = $selectedConversation?->settings->first();
    $reactionEmojis = ['👍', '❤️', '😊'];
@endphp

@section('content')
<section
    class="applicant-chat"
    data-applicant-chat
    data-current-user-id="{{ $user->id }}"
    @if ($selectedConversation)
        data-conversation-id="{{ $selectedConversation->id }}"
        data-send-url="{{ route('messages.store', $selectedConversation) }}"
        data-mark-read-url="{{ route('messages.read', $selectedConversation) }}"
        data-updates-url="{{ route('messages.updates', $selectedConversation) }}"
        data-typing-url="{{ route('messages.typing', $selectedConversation) }}"
        data-archive-url="{{ route('messages.archive', $selectedConversation) }}"
        data-delete-url="{{ route('messages.delete', $selectedConversation) }}"
        data-mute-url="{{ route('messages.mute', $selectedConversation) }}"
        data-reaction-url-template="{{ route('messages.reactions', ['conversation' => $selectedConversation, 'message' => '__message__']) }}"
        data-messages-index-url="{{ route('messages.index') }}"
    @endif
    aria-labelledby="applicant-chat-title"
>
    <div class="applicant-chat__topbar">
        <div>
            <p class="applicant-chat__eyebrow">Secure messaging</p>
            <h1 id="applicant-chat-title">Messages</h1>
            <p>{{ $messagingDescription }}</p>
        </div>
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
                    <p class="applicant-chat__list-label" data-chat-conversations-label>Conversations</p>
                    @foreach ($conversations as $conversation)
                        @php
                            $contact = $conversation->otherParticipant($user);
                            $lastMessage = $conversation->latestMessage;
                            $lastMessagePreview = $lastMessage?->body
                                ?: ($lastMessage?->attachment_mime
                                    ? (str_starts_with($lastMessage->attachment_mime, 'image/') ? 'Photo' : 'Video')
                                    : 'Start a secure conversation');
                            $isActive = $selectedConversation?->is($conversation);
                            $unreadCount = (int) ($conversation->unread_count ?? 0);
                        @endphp
                        @if ($contact)
                            <a
                                href="{{ route('messages.index', ['conversation' => $conversation->id]) }}"
                                class="applicant-chat__contact {{ $isActive ? 'is-active' : '' }}"
                                data-chat-contact
                                data-conversation-id="{{ $conversation->id }}"
                                data-unread-count="{{ $unreadCount }}"
                                data-chat-last-message-id="{{ $lastMessage?->id ?? 0 }}"
                                data-chat-search-text="{{ strtolower($chatService->displayName($contact).' '.$lastMessagePreview) }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                <span class="applicant-chat__avatar" aria-hidden="true">{{ $chatService->initials($contact) }}</span>
                                <span class="applicant-chat__contact-copy">
                                    <strong data-chat-contact-name>{{ $chatService->displayName($contact) }}</strong>
                                    <small data-chat-contact-context>{{ $contact->account_type === 'admin' ? 'Platform Support' : ($contact->account_type === 'employer' ? 'Employer' : 'PWD Applicant') }}</small>
                                    <em data-chat-contact-preview>{{ $lastMessagePreview }}</em>
                                </span>
                                <span class="applicant-chat__contact-meta">
                                    <time data-chat-contact-time @if ($lastMessage?->created_at) datetime="{{ $lastMessage->created_at->toIso8601String() }}" @else hidden @endif>{{ $lastMessage?->created_at ? ($lastMessage->created_at->isToday() ? $lastMessage->created_at->format('g:i A') : $lastMessage->created_at->format('M j')) : '' }}</time>
                                    <span class="applicant-chat__unread-badge" data-chat-contact-unread @if ($unreadCount === 0) hidden @endif>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                </span>
                            </a>
                        @endif
                    @endforeach
                @endif

                @if ($hasContacts)
                    <p class="applicant-chat__list-label" data-chat-start-label>Start a conversation</p>
                    @foreach ($contacts as $contact)
                        @php
                            $alreadyOpen = $conversations->contains(fn ($conversation) => $conversation->hasParticipant($contact));
                        @endphp
                        @unless ($alreadyOpen)
                            <form action="{{ route('messages.conversations.store') }}" method="POST" class="applicant-chat__contact-form">
                                @csrf
                                <input type="hidden" name="recipient_id" value="{{ $contact->id }}">
                                <button
                                    type="submit"
                                    class="applicant-chat__contact applicant-chat__contact--new"
                                    data-chat-contact
                                    data-recipient-id="{{ $contact->id }}"
                                    data-chat-search-text="{{ strtolower($chatService->displayName($contact).' support') }}"
                                >
                                    <span class="applicant-chat__avatar" aria-hidden="true">{{ $chatService->initials($contact) }}</span>
                                    <span class="applicant-chat__contact-copy">
                                        <strong>{{ $chatService->displayName($contact) }}</strong>
                                        <small>{{ $contact->account_type === 'admin' ? 'Platform Support' : ($contact->account_type === 'employer' ? 'Employer' : 'PWD Applicant') }}</small>
                                        <em>Start a secure conversation</em>
                                    </span>
                                    <svg class="applicant-chat__start-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                                </button>
                            </form>
                        @endunless
                    @endforeach
                @endif

                @unless ($conversations->isNotEmpty() || $hasContacts)
                    <div class="applicant-chat__no-contacts" data-chat-empty-contacts>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.5a3.5 3.5 0 0 1-3.5 3.5H8l-4 2v-12A3.5 3.5 0 0 1 7.5 5.5h9A3.5 3.5 0 0 1 20 9v6.5Z" /><path d="M8 11h8M8 14h5" /></svg>
                        <strong>No messages yet</strong>
                        <p>{{ $isEmployer ? 'New applicant conversations will appear here after someone applies to one of your jobs.' : 'Your employer conversation will appear here immediately after you apply for a job.' }}</p>
                    </div>
                @endunless
            </div>
        </aside>

        <article class="applicant-chat__conversation" aria-busy="false">
            <div class="applicant-chat__conversation-loading" data-chat-conversation-loader role="status" aria-live="polite" hidden>
                <span class="applicant-chat__conversation-loader-spinner" aria-hidden="true"></span>
                <span data-chat-conversation-loader-message>Opening conversation…</span>
            </div>
            @if ($selectedConversation && $selectedContact)
                <header class="applicant-chat__conversation-header">
                    <span class="applicant-chat__avatar applicant-chat__avatar--header" aria-hidden="true">{{ $chatService->initials($selectedContact) }}</span>
                    <span>
                        <strong>{{ $chatService->displayName($selectedContact) }}</strong>
                        <small
                            class="applicant-chat__presence"
                            data-chat-presence
                            data-last-seen-at="{{ $selectedContact->last_seen_at?->toIso8601String() }}"
                            data-chat-presence-url="{{ route('messages.presence', $selectedConversation) }}"
                        >Offline</small>
                        <small class="applicant-chat__typing" data-chat-typing role="status" aria-live="polite" aria-label="The other participant is typing" hidden>
                            <span class="applicant-chat__typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                            <span class="sr-only">Typing</span>
                        </small>
                    </span>
                    <span class="applicant-chat__secure-label">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                        Private
                    </span>
                    <details class="applicant-chat__actions">
                        <summary aria-label="Conversation options">•••</summary>
                        <div class="applicant-chat__actions-menu">
                            <strong>Notifications</strong>
                            <button type="button" data-chat-mute-duration="15m">Mute for 15 minutes</button>
                            <button type="button" data-chat-mute-duration="1h">Mute for 1 hour</button>
                            <button type="button" data-chat-mute-duration="8h">Mute for 8 hours</button>
                            <button type="button" data-chat-mute-duration="forever">Mute until turned on</button>
                            <button type="button" data-chat-mute-duration="off">Turn notifications on</button>
                            <small data-chat-settings-status>
                                @if ($selectedSettings?->muted_until?->isFuture())
                                    Notifications muted
                                @else
                                    Notifications on
                                @endif
                            </small>
                            <hr>
                            <button type="button" class="is-danger" data-chat-archive>Archive Conversation</button>
                            <button type="button" class="is-danger" data-chat-delete>Delete Conversation</button>
                        </div>
                    </details>
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
                            @php
                                $isMine = $message->sender_id === $user->id;
                                $reactionsByEmoji = collect($chatService->messageReactionsPayload($message))->keyBy('emoji');
                            @endphp
                            <div class="applicant-chat__message {{ $isMine ? 'is-mine' : '' }}" data-message-id="{{ $message->id }}" data-message-sender-id="{{ $message->sender_id }}">
                                @unless ($isMine)
                                    <span class="applicant-chat__message-avatar" aria-hidden="true">{{ $chatService->initials($message->sender) }}</span>
                                @endunless
                                <div>
                                    @unless ($isMine)
                                        <strong>{{ $chatService->displayName($message->sender) }}</strong>
                                    @endunless
                                    @if ($message->attachment_path)
                                        <div class="applicant-chat__message-media">
                                            @if (str_starts_with((string) $message->attachment_mime, 'image/'))
                                                <img src="{{ route('messages.attachment', ['conversation' => $selectedConversation, 'message' => $message]) }}" alt="{{ $message->attachment_name ?: 'Attached image' }}" loading="lazy">
                                            @else
                                                <video controls preload="metadata">
                                                    <source src="{{ route('messages.attachment', ['conversation' => $selectedConversation, 'message' => $message]) }}" type="{{ $message->attachment_mime }}">
                                                    Your browser cannot play this video.
                                                </video>
                                            @endif
                                            <small>{{ $message->attachment_name }}</small>
                                        </div>
                                    @endif
                                    @if (filled($message->body))
                                        <p>{{ $message->body }}</p>
                                    @endif
                                    <time data-chat-message-time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('g:i A') }}</time>
                                    @if ($isMine)
                                        <span class="applicant-chat__message-status" data-message-status data-read-at="{{ $message->read_at?->toIso8601String() }}">{{ $message->read_at ? 'Seen' : 'Sent' }}</span>
                                    @endif
                                    <div class="applicant-chat__message-reactions" data-message-reactions @if ($reactionsByEmoji->isEmpty()) hidden @endif>
                                        @foreach ($reactionsByEmoji as $reaction)
                                            <span class="applicant-chat__reaction-chip {{ in_array($user->id, $reaction['user_ids'], true) ? 'is-active' : '' }}">
                                                <span>{{ $reaction['emoji'] }}</span>
                                                <b>{{ $reaction['count'] }}</b>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <form class="applicant-chat__composer" data-chat-send-form novalidate>
                    @csrf
                    <label class="sr-only" for="chat-message-body">Message {{ $chatService->displayName($selectedContact) }}</label>
                    <label class="applicant-chat__attach-button" title="Attach image or video">
                        <span class="sr-only">Attach image or video</span>
                        <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime" data-chat-attachment-input>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.4 11.6-8.7 8.7a6 6 0 0 1-8.5-8.5l9.4-9.4a4.1 4.1 0 0 1 5.8 5.8L10 17.6a2.2 2.2 0 0 1-3.1-3.1l8.7-8.7" /></svg>
                    </label>
                    <textarea id="chat-message-body" name="body" rows="1" maxlength="2000" data-chat-message-input placeholder="Write a message..."></textarea>
                    <button type="submit" data-chat-send-button aria-label="Send message">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-8.5 18-3.4-7.1L2 10.5 21 3Z" /><path d="m9.1 13.9 4.3-4.3" /></svg>
                        <span>Send</span>
                    </button>
                    <p class="applicant-chat__composer-status" data-chat-composer-status role="status"></p>
                </form>
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
