@extends($user->account_type === 'admin' ? 'layout.admin' : ($user->account_type === 'employer' ? 'layout.employer' : 'layout.dashboard'))

@php
    $messageCount = $selectedConversation?->messages->count() ?? 0;
    $hasContacts = $contacts->isNotEmpty();
    $isEmployer = $user->account_type === 'employer';
    $isAdmin = $user->account_type === 'admin';
    $messagingDescription = $isAdmin
        ? 'Respond to active PWD applicants and verified employers from one secure inbox.'
        : ($isEmployer
        ? 'Message applicants after they apply, or contact platform support securely.'
        : 'Message platform support and employers after you apply for a job.');
    $selectedSettings = $selectedConversation?->settings->first();
    $reactionEmojis = ['👍', '❤️', '😊'];
    $selectedApplication = $selectedConversation?->latestJobApplication;
    $hiringStages = \App\Models\JobApplication::hiringStages();
    $hiringTimeline = $selectedApplication?->hiringStagePayload();
    $canUpdateHiringStage = $isEmployer
        && $selectedApplication
        && (int) $selectedApplication->job?->user_id === (int) $user->id;
@endphp

@if ($isAdmin)
    @section('breadcrumb')
        <i data-lucide="house"></i>
        <i data-lucide="chevron-right"></i>
        <strong>Messages</strong>
    @endsection
@endif

@section('content')
<section
    class="applicant-chat {{ $isEmployer ? 'applicant-chat--employer' : '' }}"
    data-applicant-chat
    data-current-user-id="{{ $user->id }}"
    data-account-type="{{ $user->account_type }}"
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
        data-requirements-acknowledge-url-template="{{ route('messages.requirements.acknowledge', ['conversation' => $selectedConversation, 'message' => '__message__']) }}"
        @if ($canUpdateHiringStage)
            data-hiring-actions-url="{{ route('messages.hiring-actions.store', $selectedConversation) }}"
        @endif
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
                                <span class="applicant-chat__avatar {{ $contact->profile_photo_url ? 'has-image' : '' }}" data-chat-avatar-user-id="{{ $contact->id }}" aria-hidden="true">
                                    @if ($contact->profile_photo_url)<img src="{{ $contact->profile_photo_url }}" alt="">@else{{ $chatService->initials($contact) }}@endif
                                </span>
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
                                    <span class="applicant-chat__avatar {{ $contact->profile_photo_url ? 'has-image' : '' }}" data-chat-avatar-user-id="{{ $contact->id }}" aria-hidden="true">
                                        @if ($contact->profile_photo_url)<img src="{{ $contact->profile_photo_url }}" alt="">@else{{ $chatService->initials($contact) }}@endif
                                    </span>
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
                <div class="applicant-chat__conversation-top">
                    <header class="applicant-chat__conversation-header">
                        <span class="applicant-chat__avatar applicant-chat__avatar--header {{ $selectedContact->profile_photo_url ? 'has-image' : '' }}" data-chat-avatar-user-id="{{ $selectedContact->id }}" aria-hidden="true">
                            @if ($selectedContact->profile_photo_url)<img src="{{ $selectedContact->profile_photo_url }}" alt="">@else{{ $chatService->initials($selectedContact) }}@endif
                        </span>
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

                    @if ($hiringTimeline)
                        <section
                            class="applicant-chat__hiring-timeline"
                            data-hiring-timeline
                            data-current-stage="{{ $hiringTimeline['number'] }}"
                            aria-labelledby="hiring-timeline-title"
                        >
                            <div class="applicant-chat__hiring-timeline-heading">
                                <div>
                                    <p>Application progress</p>
                                    <h2 id="hiring-timeline-title" data-hiring-stage-title>{{ $hiringTimeline['title'] }}</h2>
                                    <small data-hiring-stage-progress>Step {{ $hiringTimeline['number'] }} of {{ count($hiringStages) }}</small>
                                </div>
                                @if ($canUpdateHiringStage)
                                    <form action="{{ route('messages.application-stage', $selectedConversation) }}" method="POST" data-hiring-stage-form>
                                        @csrf
                                        @method('PATCH')
                                        <label for="hiring-stage-status">Update stage</label>
                                        <span>
                                            <select id="hiring-stage-status" name="status" data-hiring-stage-select>
                                                @foreach ($hiringStages as $status => $stage)
                                                    <option value="{{ $status }}" @selected($hiringTimeline['status'] === $status)>{{ $stage['title'] }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" data-hiring-stage-submit>Save</button>
                                        </span>
                                        <small data-hiring-stage-status role="status"></small>
                                    </form>
                                @endif
                            </div>

                            <ol class="applicant-chat__hiring-stages" style="--hiring-progress: {{ (($hiringTimeline['number'] - 1) / max(1, count($hiringStages) - 1)) * 80 }}%;">
                                @foreach ($hiringStages as $stage)
                                    @php
                                        $isCurrentStage = $stage['number'] === $hiringTimeline['number'];
                                        $isCompletedStage = $stage['number'] < $hiringTimeline['number'];
                                    @endphp
                                    <li
                                        class="applicant-chat__hiring-stage {{ $isCurrentStage ? 'is-current' : '' }} {{ $isCompletedStage ? 'is-completed' : '' }}"
                                        data-hiring-stage
                                        data-stage-number="{{ $stage['number'] }}"
                                        @if ($isCurrentStage) aria-current="step" @endif
                                    >
                                        <span class="applicant-chat__hiring-stage-number">{{ $stage['number'] }}</span>
                                        <div>
                                            <strong>{{ $stage['short_label'] }}</strong>
                                            <span class="sr-only" data-hiring-stage-state>{{ $isCurrentStage ? 'Current stage' : ($isCompletedStage ? 'Completed stage' : 'Upcoming stage') }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                </div>

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
                                $isRequirementsCard = $message->message_type === \App\Models\Message::TYPE_REQUIREMENTS_CARD;
                                $isHiringActionCard = $message->message_type === \App\Models\Message::TYPE_HIRING_ACTION;
                                $requirementsCard = $isRequirementsCard ? ($message->metadata ?? []) : [];
                                $hiringActionCard = $isHiringActionCard ? ($message->metadata ?? []) : [];
                                $requirements = collect($requirementsCard['items'] ?? [])
                                    ->filter(fn ($item) => is_array($item) && filled($item['label'] ?? null));
                                $hiringActionDetails = collect($hiringActionCard['details'] ?? [])
                                    ->filter(fn ($item) => is_array($item) && filled($item['label'] ?? null) && filled($item['value'] ?? null));
                                $hiringActionItems = collect($hiringActionCard['items'] ?? [])
                                    ->filter(fn ($item) => is_string($item) && filled($item));
                                $requirementsAcknowledged = filled($requirementsCard['acknowledged_at'] ?? null);
                                $canAcknowledgeRequirements = $isRequirementsCard
                                    && ! $isMine
                                    && ! $requirementsAcknowledged
                                    && $user->account_type === 'pwd_applicant'
                                    && (int) ($selectedApplication?->applicant_id ?? 0) === (int) $user->id;
                            @endphp
                            <div class="applicant-chat__message {{ $isMine ? 'is-mine' : '' }} {{ $isRequirementsCard ? 'has-requirements-card' : '' }} {{ $isHiringActionCard ? 'has-hiring-card' : '' }}" data-message-id="{{ $message->id }}" data-message-sender-id="{{ $message->sender_id }}">
                                @unless ($isMine)
                                    <span class="applicant-chat__message-avatar {{ $message->sender->profile_photo_url ? 'has-image' : '' }}" data-chat-avatar-user-id="{{ $message->sender_id }}" aria-hidden="true">
                                        @if ($message->sender->profile_photo_url)<img src="{{ $message->sender->profile_photo_url }}" alt="">@else{{ $chatService->initials($message->sender) }}@endif
                                    </span>
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
                                    @if ($isRequirementsCard)
                                        <section class="applicant-chat__requirements-card" data-requirements-card data-message-id="{{ $message->id }}" aria-label="Mga requirement para sa aplikasyon">
                                            <header>
                                                <span class="applicant-chat__requirements-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 4.5h6M9 3h6a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 15 7H9a1.5 1.5 0 0 1-1.5-1.5v-1A1.5 1.5 0 0 1 9 3Z" /><path d="M7 5.5H5.75A1.75 1.75 0 0 0 4 7.25v12A1.75 1.75 0 0 0 5.75 21h12a1.75 1.75 0 0 0 1.75-1.75v-12A1.75 1.75 0 0 0 17.75 5.5H16" /><path d="m8 13 2 2 5-5" /></svg></span>
                                                <span>
                                                    <p>Checklist ng kailangan</p>
                                                    <h3>{{ $requirementsCard['heading'] ?? 'Mga kailangang ihanda' }}</h3>
                                                </span>
                                                @if ($requirementsAcknowledged)
                                                    <b class="is-confirmed">Nabasa na</b>
                                                @endif
                                            </header>
                                            @if (filled($requirementsCard['job_title'] ?? null))
                                                <p class="applicant-chat__requirements-job">Para sa: {{ $requirementsCard['job_title'] }}</p>
                                            @endif
                                            <p class="applicant-chat__requirements-intro">{{ $requirementsCard['intro'] ?? 'Pakihanda ang mga sumusunod para sa iyong aplikasyon.' }}</p>
                                            <ul class="applicant-chat__requirements-list">
                                                @forelse ($requirements as $requirement)
                                                    <li>
                                                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12.5 4.2 4L19 6.8" /></svg></span>
                                                        <div><strong>{{ $requirement['label'] }}</strong><small>{{ $requirement['status'] ?? 'Kailangang ihanda' }}</small></div>
                                                    </li>
                                                @empty
                                                    <li><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12.5 4.2 4L19 6.8" /></svg></span><div><strong>Hintayin ang susunod na mensahe ng employer.</strong><small>Wala pang dagdag na dokumento</small></div></li>
                                                @endforelse
                                            </ul>
                                            @if ($canAcknowledgeRequirements)
                                                <button type="button" class="applicant-chat__requirements-confirm" data-requirements-acknowledge data-requirements-acknowledge-url="{{ route('messages.requirements.acknowledge', ['conversation' => $selectedConversation, 'message' => $message]) }}">Nabasa ko, ihahanda ko ito</button>
                                            @elseif ($requirementsAcknowledged)
                                                <p class="applicant-chat__requirements-confirmed">Nabasa na ng applicant at ihahanda niya ang mga requirement.</p>
                                            @endif
                                        </section>
                                    @elseif ($isHiringActionCard)
                                        <section class="applicant-chat__hiring-card applicant-chat__hiring-card--{{ $hiringActionCard['action'] ?? 'notice' }}" aria-label="{{ $hiringActionCard['title'] ?? 'Hiring update' }}">
                                            <header>
                                                <span class="applicant-chat__hiring-card-icon" aria-hidden="true">
                                                    @if (($hiringActionCard['action'] ?? null) === 'documents')
                                                        <svg viewBox="0 0 24 24"><path d="M7 3.5h7l3 3V20.5H7z" /><path d="M14 3.5v4h3M9.5 12h5M9.5 15.5h5" /></svg>
                                                    @elseif (($hiringActionCard['action'] ?? null) === 'assessment')
                                                        <svg viewBox="0 0 24 24"><path d="M4 5.5h16v13H4z" /><path d="M8 9h8M8 12h4M8 15h5" /></svg>
                                                    @else
                                                        <svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2" /><path d="M8 3v4M16 3v4M4 10h16M8 14h3M8 17h6" /></svg>
                                                    @endif
                                                </span>
                                                <span><p>{{ $hiringActionCard['eyebrow'] ?? 'Hiring update' }}</p><h3>{{ $hiringActionCard['title'] ?? 'May bagong update ang employer' }}</h3></span>
                                            </header>
                                            @if (filled($hiringActionCard['intro'] ?? null))<p class="applicant-chat__hiring-card-intro">{{ $hiringActionCard['intro'] }}</p>@endif
                                            @if ($hiringActionDetails->isNotEmpty())
                                                <dl class="applicant-chat__hiring-card-details">
                                                    @foreach ($hiringActionDetails as $detail)<div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] }}</dd></div>@endforeach
                                                </dl>
                                            @endif
                                            @if ($hiringActionItems->isNotEmpty())
                                                <ul class="applicant-chat__hiring-card-items">@foreach ($hiringActionItems as $item)<li>{{ $item }}</li>@endforeach</ul>
                                            @endif
                                        </section>
                                    @elseif (filled($message->body))
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
                    <div class="applicant-chat__attachment-preview" data-chat-attachment-preview hidden></div>
                    @if ($canUpdateHiringStage)
                        <details class="applicant-chat__hiring-actions" data-hiring-actions>
                            <summary aria-label="Hiring actions" title="Hiring actions"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M3 12h18M5.5 5.5l13 13M18.5 5.5l-13 13" /></svg></summary>
                            <div class="applicant-chat__hiring-actions-menu">
                                <p>Hiring actions</p>
                                <button type="button" data-hiring-action-open="interview"><b>01</b><span><strong>Mag-schedule ng interview</strong><small>Itakda ang petsa, oras, at paraan</small></span></button>
                                <button type="button" data-hiring-action-open="assessment"><b>02</b><span><strong>Magpadala ng skills assessment</strong><small>Magbigay ng assessment invitation</small></span></button>
                                <button type="button" data-hiring-action-open="documents"><b>03</b><span><strong>Humingi ng documents</strong><small>Gumawa ng checklist para sa applicant</small></span></button>
                            </div>
                        </details>
                    @endif
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
                @if ($canUpdateHiringStage)
                    <dialog class="applicant-chat__hiring-dialog" data-hiring-action-dialog aria-labelledby="hiring-action-dialog-title">
                        <form data-hiring-action-form novalidate>
                            @csrf
                            <header>
                                <div><p>Hiring action</p><h2 id="hiring-action-dialog-title" data-hiring-action-title>Mag-schedule ng interview</h2><span data-hiring-action-description>Ilagay ang detalye bago ipadala sa applicant.</span></div>
                                <button type="button" data-hiring-action-close aria-label="Isara">×</button>
                            </header>
                            <input type="hidden" name="action" value="interview" data-hiring-action-input>
                            <div class="applicant-chat__hiring-dialog-fields" data-hiring-action-fields="interview">
                                <label><span>Petsa *</span><input type="date" name="interview_date" required></label>
                                <label><span>Oras *</span><input type="time" name="interview_time" required></label>
                                <label><span>Paraan *</span><select name="interview_method" required><option value="Video call">Video call</option><option value="Phone call">Phone call</option><option value="In person">In person</option></select></label>
                                <label><span>Meeting link</span><input type="url" name="meeting_link" placeholder="https://..."></label>
                                <label class="is-wide"><span>Karagdagang detalye</span><textarea name="details" rows="3" maxlength="1500" placeholder="Halimbawa: Ihanda ang portfolio o anumang kailangan sa interview."></textarea></label>
                            </div>
                            <div class="applicant-chat__hiring-dialog-fields" data-hiring-action-fields="assessment" hidden>
                                <label class="is-wide"><span>Pangalan ng assessment *</span><input name="assessment_title" maxlength="160" placeholder="Halimbawa: Basic data-entry assessment" disabled required></label>
                                <label class="is-wide"><span>Assessment link</span><input type="url" name="assessment_link" placeholder="https://..." disabled></label>
                                <label class="is-wide"><span>Panuto *</span><textarea name="assessment_instructions" rows="4" maxlength="1500" placeholder="Ilagay kung paano sasagutan at kailan dapat matapos." disabled required></textarea></label>
                            </div>
                            <div class="applicant-chat__hiring-dialog-fields" data-hiring-action-fields="documents" hidden>
                                <label class="is-wide"><span>Pamagat ng document request *</span><input name="documents_title" maxlength="160" placeholder="Halimbawa: Pre-employment documents" disabled required></label>
                                <label class="is-wide"><span>Mga dokumento *</span><textarea name="documents" rows="5" maxlength="3000" placeholder="Isang dokumento bawat linya&#10;Valid ID&#10;NBI Clearance" disabled required></textarea></label>
                                <label class="is-wide"><span>Note para sa applicant</span><textarea name="documents_note" rows="3" maxlength="1500" placeholder="Dagdag na paliwanag o deadline" disabled></textarea></label>
                            </div>
                            <footer><p data-hiring-action-status role="status"></p><span><button type="button" data-hiring-action-close>Cancel</button><button type="submit" data-hiring-action-submit>Ipadala sa applicant</button></span></footer>
                        </form>
                    </dialog>
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
