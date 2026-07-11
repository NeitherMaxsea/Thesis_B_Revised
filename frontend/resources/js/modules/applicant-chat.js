let chatPresenceTimer;

// Secure one-to-one chat. Laravel persists messages first; Echo only mirrors the
// persisted payload into the existing interface.
export const initApplicantChat = () => {
    const chat = document.querySelector('[data-applicant-chat]');

    if (!chat || chat.dataset.chatInitialized === 'true') {
        return;
    }

    chat.dataset.chatInitialized = 'true';

    const currentUserId = Number(chat.dataset.currentUserId);
    const conversationId = chat.dataset.conversationId;
    const sendUrl = chat.dataset.sendUrl;
    const markReadUrl = chat.dataset.markReadUrl;
    const messages = chat.querySelector('[data-chat-messages]');
    const liveStatus = chat.querySelector('[data-chat-live-status]');
    const composerStatus = chat.querySelector('[data-chat-composer-status]');
    const search = chat.querySelector('[data-chat-search]');
    const conversationList = chat.querySelector('[data-chat-conversation-list]');
    const conversationPanel = chat.querySelector('.applicant-chat__conversation');
    const conversationLoader = chat.querySelector('[data-chat-conversation-loader]');
    const conversationLoaderMessage = chat.querySelector('[data-chat-conversation-loader-message]');
    const presence = chat.querySelector('[data-chat-presence]');
    const presenceUrl = presence?.dataset.chatPresenceUrl;
    const pendingConversationMessages = new Map();

    const ensureComposer = () => {
        if (!conversationPanel || !conversationId || !sendUrl || chat.querySelector('[data-chat-send-form]')) {
            return;
        }

        const composer = document.createElement('form');
        composer.className = 'applicant-chat__composer';
        composer.dataset.chatSendForm = '';
        composer.noValidate = true;

        const label = document.createElement('label');
        label.className = 'sr-only';
        label.htmlFor = 'chat-message-body';
        label.textContent = 'Write a message';

        const input = document.createElement('textarea');
        input.id = 'chat-message-body';
        input.name = 'body';
        input.rows = 1;
        input.maxLength = 2000;
        input.required = true;
        input.placeholder = 'Write a message...';
        input.dataset.chatMessageInput = '';

        const button = document.createElement('button');
        button.type = 'submit';
        button.dataset.chatSendButton = '';
        button.setAttribute('aria-label', 'Send message');
        button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-8.5 18-3.4-7.1L2 10.5 21 3Z"></path><path d="m9.1 13.9 4.3-4.3"></path></svg><span>Send</span>';

        const status = document.createElement('p');
        status.className = 'applicant-chat__composer-status';
        status.dataset.chatComposerStatus = '';
        status.setAttribute('role', 'status');

        composer.append(label, input, button, status);
        conversationPanel.append(composer);
    };

    ensureComposer();

    const form = chat.querySelector('[data-chat-send-form]');
    const input = chat.querySelector('[data-chat-message-input]');
    const sendButton = chat.querySelector('[data-chat-send-button]');
    const activeComposerStatus = chat.querySelector('[data-chat-composer-status]');

    let readInFlight = false;
    let readQueued = false;
    let conversationNavigationInProgress = false;

    const isPageVisible = () => document.visibilityState !== 'hidden';
    const getContacts = () => [...chat.querySelectorAll('[data-chat-contact]')];
    const findConversationContact = (id) => getContacts()
        .find((contact) => contact.dataset.conversationId === String(id));

    const setLiveStatus = (message, state = 'ready') => {
        if (!liveStatus) {
            return;
        }

        liveStatus.classList.remove('is-ready', 'is-error');
        liveStatus.classList.add(`is-${state}`);
        liveStatus.lastChild.textContent = ` ${message}`;
    };

    const setComposerStatus = (message = '', isError = false) => {
        if (!activeComposerStatus) {
            return;
        }

        activeComposerStatus.textContent = message;
        activeComposerStatus.classList.toggle('is-error', isError);
    };

    const scrollToLatestMessage = () => {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    };

    const parseDate = (isoTime) => {
        const date = isoTime ? new Date(isoTime) : null;

        return date && !Number.isNaN(date.getTime()) ? date : null;
    };

    const formatTime = (isoTime) => {
        const date = parseDate(isoTime);

        if (!date) {
            return '';
        }

        return new Intl.DateTimeFormat(undefined, {
            hour: 'numeric',
            minute: '2-digit',
        }).format(date);
    };

    const formatContactTime = (isoTime) => {
        const date = parseDate(isoTime);

        if (!date) {
            return '';
        }

        const now = new Date();
        const isToday = date.getFullYear() === now.getFullYear()
            && date.getMonth() === now.getMonth()
            && date.getDate() === now.getDate();

        return isToday
            ? formatTime(isoTime)
            : new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric' }).format(date);
    };

    const formatPresence = (isoTime) => {
        const lastSeen = parseDate(isoTime);

        if (!lastSeen) {
            return { text: 'Offline', state: 'offline' };
        }

        const elapsedMinutes = Math.max(0, Math.floor((Date.now() - lastSeen.getTime()) / 60000));

        if (elapsedMinutes < 2) {
            return { text: 'Active now', state: 'online' };
        }

        if (elapsedMinutes < 60) {
            return {
                text: `Offline · ${elapsedMinutes} ${elapsedMinutes === 1 ? 'minute' : 'minutes'} ago`,
                state: 'offline',
            };
        }

        const elapsedHours = Math.floor(elapsedMinutes / 60);

        if (elapsedHours < 24) {
            return {
                text: `Offline · ${elapsedHours} ${elapsedHours === 1 ? 'hour' : 'hours'} ago`,
                state: 'offline',
            };
        }

        const elapsedDays = Math.floor(elapsedHours / 24);

        return {
            text: `Offline · ${elapsedDays} ${elapsedDays === 1 ? 'day' : 'days'} ago`,
            state: 'offline',
        };
    };

    const setPresence = (lastSeenAt) => {
        if (!presence) {
            return;
        }

        const status = formatPresence(lastSeenAt);
        presence.dataset.lastSeenAt = lastSeenAt ?? '';
        presence.dataset.presenceState = status.state;
        presence.textContent = status.text;
    };

    const refreshPresence = async () => {
        if (!presenceUrl || !presence || !chat.isConnected) {
            return;
        }

        try {
            const response = await window.axios.get(presenceUrl);
            setPresence(response.data?.last_seen_at ?? '');
        } catch {
            // The current status remains visible until the next successful poll.
        }
    };

    const localizeRenderedTimes = () => {
        chat.querySelectorAll('[data-chat-message-time]').forEach((time) => {
            time.textContent = formatTime(time.dateTime);
        });

        chat.querySelectorAll('[data-chat-contact-time]').forEach((time) => {
            time.textContent = formatContactTime(time.dateTime);
            time.hidden = !time.textContent;
        });
    };

    const ensureConversationsLabel = () => {
        if (!conversationList) {
            return null;
        }

        let label = conversationList.querySelector('[data-chat-conversations-label]');

        if (!label) {
            label = document.createElement('p');
            label.className = 'applicant-chat__list-label';
            label.dataset.chatConversationsLabel = '';
            label.textContent = 'Conversations';
            conversationList.insertBefore(
                label,
                conversationList.querySelector('[data-chat-start-label]') ?? conversationList.firstChild,
            );
        }

        return label;
    };

    const moveContactToTop = (contact) => {
        if (!conversationList || !contact) {
            return;
        }

        ensureConversationsLabel();

        const firstConversation = [...conversationList.children]
            .find((child) => child.matches?.('[data-chat-contact][data-conversation-id]'));

        if (firstConversation && firstConversation !== contact) {
            conversationList.insertBefore(contact, firstConversation);
        } else if (!firstConversation) {
            conversationList.insertBefore(
                contact,
                conversationList.querySelector('[data-chat-start-label]') ?? null,
            );
        }
    };

    const setContactUnread = (contact, count) => {
        if (!contact) {
            return 0;
        }

        const previous = Math.max(0, Number(contact.dataset.unreadCount) || 0);
        const next = Math.max(0, Number(count) || 0);
        const badge = contact.querySelector('[data-chat-contact-unread]');

        contact.dataset.unreadCount = String(next);
        contact.classList.toggle('has-unread', next > 0);

        if (badge) {
            badge.textContent = next > 99 ? '99+' : String(next);
            badge.hidden = next === 0;
        }

        return previous;
    };

    const updateContactSearchText = (contact) => {
        const name = contact.querySelector('[data-chat-contact-name]')?.textContent ?? '';
        const context = contact.querySelector('[data-chat-contact-context]')?.textContent ?? '';
        const preview = contact.querySelector('[data-chat-contact-preview]')?.textContent ?? '';

        contact.dataset.chatSearchText = `${name} ${context} ${preview}`.toLowerCase();
    };

    const syncConversationContact = (message, incrementUnread = false) => {
        const targetConversationId = String(message?.conversation_id ?? '');

        if (!targetConversationId) {
            return;
        }

        const contact = findConversationContact(targetConversationId);

        if (!contact) {
            const pending = pendingConversationMessages.get(targetConversationId) ?? {
                message: null,
                unreadCount: 0,
            };

            pending.message = message;
            pending.unreadCount += incrementUnread ? 1 : 0;
            pendingConversationMessages.set(targetConversationId, pending);
            return;
        }

        const preview = contact.querySelector('[data-chat-contact-preview]');
        const time = contact.querySelector('[data-chat-contact-time]');

        if (preview && typeof message.body === 'string') {
            preview.textContent = message.body;
        }

        if (time) {
            time.dateTime = message.sent_at ?? '';
            time.textContent = formatContactTime(message.sent_at);
            time.hidden = !time.textContent;
        }

        if (incrementUnread) {
            setContactUnread(contact, (Number(contact.dataset.unreadCount) || 0) + 1);
        }

        updateContactSearchText(contact);
        moveContactToTop(contact);
    };

    const createMessage = (message) => {
        if (
            !messages
            || !message?.id
            || !message?.sender
            || messages.querySelector(`[data-message-id="${Number(message.id)}"]`)
        ) {
            return false;
        }

        messages.querySelector('[data-chat-empty]')?.remove();

        const isMine = Number(message.sender.id) === currentUserId;
        const row = document.createElement('div');
        row.className = `applicant-chat__message${isMine ? ' is-mine' : ''}`;
        row.dataset.messageId = String(message.id);
        row.dataset.messageSenderId = String(message.sender.id);

        if (!isMine) {
            const avatar = document.createElement('span');
            avatar.className = 'applicant-chat__message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            avatar.textContent = message.sender.initials ?? '';
            row.append(avatar);
        }

        const content = document.createElement('div');

        if (!isMine) {
            const sender = document.createElement('strong');
            sender.textContent = message.sender.name ?? '';
            content.append(sender);
        }

        const body = document.createElement('p');
        body.textContent = message.body ?? '';
        const time = document.createElement('time');
        time.dataset.chatMessageTime = '';
        time.dateTime = message.sent_at ?? '';
        time.textContent = formatTime(message.sent_at);
        content.append(body, time);

        if (isMine) {
            const status = document.createElement('span');
            const isRead = Boolean(message.is_read || message.read_at);
            status.className = 'applicant-chat__message-status';
            status.dataset.messageStatus = '';
            status.dataset.readAt = message.read_at ?? '';
            status.textContent = isRead ? 'Seen' : 'Sent';
            content.append(status);
        }

        row.append(content);
        messages.append(row);
        scrollToLatestMessage();

        return true;
    };

    const setMessageSeen = (messageId, readAt = '') => {
        if (!messages) {
            return;
        }

        const row = [...messages.querySelectorAll('[data-message-id]')]
            .find((candidate) => candidate.dataset.messageId === String(messageId));

        if (!row || Number(row.dataset.messageSenderId) !== currentUserId) {
            return;
        }

        const status = row.querySelector('[data-message-status]');

        if (status) {
            status.textContent = 'Seen';
            status.dataset.readAt = readAt ?? '';
        }
    };

    const clearActiveConversationUnread = () => {
        const contact = findConversationContact(conversationId);
        const clearedCount = setContactUnread(contact, 0);

        if (clearedCount > 0) {
            window.dispatchEvent(new CustomEvent('chat:conversation-read', {
                detail: {
                    conversationId: String(conversationId),
                    clearedCount,
                },
            }));
        }
    };

    const markConversationRead = async () => {
        if (!conversationId || !markReadUrl || !isPageVisible()) {
            return;
        }

        if (readInFlight) {
            readQueued = true;
            return;
        }

        readInFlight = true;

        try {
            await window.axios.patch(markReadUrl, {}, {
                headers: { Accept: 'application/json' },
            });
            clearActiveConversationUnread();
        } catch {
            // Reading is retried on the next incoming message or visibility change.
        } finally {
            readInFlight = false;

            if (readQueued) {
                readQueued = false;
                markConversationRead();
            }
        }
    };

    const handleConversationMessage = (message) => {
        if (String(message?.conversation_id ?? '') !== String(conversationId ?? '')) {
            return;
        }

        createMessage(message);
        syncConversationContact(message);

        if (Number(message.sender?.id) !== currentUserId) {
            markConversationRead();
        }
    };

    const safeConversationUrl = (value, targetConversationId) => {
        try {
            const url = value
                ? new URL(value, window.location.origin)
                : new URL(window.location.href);

            if (url.origin !== window.location.origin) {
                return null;
            }

            if (!value) {
                url.search = '';
                url.searchParams.set('conversation', targetConversationId);
            }

            return url.href;
        } catch {
            return null;
        }
    };

    const setConversationLoading = (isLoading, message = 'Opening conversation…') => {
        if (conversationPanel) {
            conversationPanel.setAttribute('aria-busy', String(isLoading));
        }

        if (conversationLoader) {
            conversationLoader.hidden = !isLoading;
        }

        if (conversationLoaderMessage) {
            conversationLoaderMessage.textContent = message;
        }
    };

    const openConversation = async (url, options = {}) => {
        if (conversationNavigationInProgress) {
            return;
        }

        const targetUrl = new URL(url, window.location.href);

        if (targetUrl.href === window.location.href) {
            return;
        }

        conversationNavigationInProgress = true;
        setConversationLoading(true);

        try {
            const response = await fetch(targetUrl.href, {
                method: options.method ?? 'GET',
                body: options.body,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Unable to open the selected conversation.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextChat = page.querySelector('[data-applicant-chat]');

            if (!nextChat) {
                throw new Error('The selected conversation could not be loaded.');
            }

            if (conversationId && window.Echo) {
                window.Echo.leave(`chat.conversation.${conversationId}`);
            }

            chat.replaceWith(nextChat);
            document.title = page.title || document.title;
            window.history.pushState({}, '', response.url || targetUrl.href);
            initApplicantChat();
        } catch {
            setConversationLoading(false, 'Could not open the conversation. Please try again.');
            setLiveStatus('Conversation could not be opened', 'error');
        } finally {
            conversationNavigationInProgress = false;
        }
    };

    const createApplicationConversation = (application) => {
        if (!conversationList || !application?.conversation_id || !application?.applicant) {
            return;
        }

        const targetConversationId = String(application.conversation_id);
        const existing = findConversationContact(targetConversationId);
        const contextText = `PWD Applicant${application.job_title ? ` · ${application.job_title}` : ''}`;

        if (existing) {
            const context = existing.querySelector('[data-chat-contact-context]');
            if (context) context.textContent = contextText;
            updateContactSearchText(existing);
            moveContactToTop(existing);

            const startContact = getContacts()
                .find((candidate) => candidate.dataset.recipientId === String(application.applicant.id));
            startContact?.closest('form')?.remove();
            return;
        }

        const href = safeConversationUrl(application.conversation_url, targetConversationId);

        if (!href) {
            return;
        }

        conversationList.querySelector('[data-chat-empty-contacts]')?.remove();
        ensureConversationsLabel();

        const contact = document.createElement('a');
        contact.href = href;
        contact.className = 'applicant-chat__contact';
        contact.dataset.chatContact = '';
        contact.dataset.conversationId = targetConversationId;
        contact.dataset.unreadCount = '0';

        const avatar = document.createElement('span');
        avatar.className = 'applicant-chat__avatar';
        avatar.setAttribute('aria-hidden', 'true');
        avatar.textContent = application.applicant.initials ?? '';

        const copy = document.createElement('span');
        copy.className = 'applicant-chat__contact-copy';
        const name = document.createElement('strong');
        name.dataset.chatContactName = '';
        name.textContent = application.applicant.name ?? 'Applicant';
        const context = document.createElement('small');
        context.dataset.chatContactContext = '';
        context.textContent = contextText;
        const preview = document.createElement('em');
        preview.dataset.chatContactPreview = '';
        preview.textContent = 'New job application';
        copy.append(name, context, preview);

        const metadata = document.createElement('span');
        metadata.className = 'applicant-chat__contact-meta';
        const time = document.createElement('time');
        time.dataset.chatContactTime = '';
        time.dateTime = application.applied_at ?? '';
        time.textContent = formatContactTime(application.applied_at);
        time.hidden = !time.textContent;
        const unread = document.createElement('span');
        unread.className = 'applicant-chat__unread-badge';
        unread.dataset.chatContactUnread = '';
        unread.hidden = true;
        unread.textContent = '0';
        metadata.append(time, unread);
        contact.append(avatar, copy, metadata);

        conversationList.insertBefore(
            contact,
            conversationList.querySelector('[data-chat-start-label]') ?? null,
        );
        updateContactSearchText(contact);
        moveContactToTop(contact);

        const startContact = getContacts()
            .find((candidate) => candidate.dataset.recipientId === String(application.applicant.id));
        startContact?.closest('form')?.remove();

        if (!conversationList.querySelector('[data-chat-contact][data-recipient-id]')) {
            conversationList.querySelector('[data-chat-start-label]')?.remove();
        }

        const pending = pendingConversationMessages.get(targetConversationId);

        if (pending) {
            syncConversationContact(pending.message);
            setContactUnread(contact, pending.unreadCount);
            pendingConversationMessages.delete(targetConversationId);
        }
    };

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();

        getContacts().forEach((contact) => {
            contact.hidden = query !== '' && !contact.dataset.chatSearchText.includes(query);
        });
    });

    chat.addEventListener('click', (event) => {
        if (
            event.defaultPrevented
            || !(event.target instanceof Element)
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        const contact = event.target.closest('a[data-chat-contact][data-conversation-id]');

        if (!contact || !chat.contains(contact)) {
            return;
        }

        event.preventDefault();
        openConversation(contact.href);
    });

    chat.querySelectorAll('.applicant-chat__contact-form').forEach((startForm) => {
        startForm.addEventListener('submit', (event) => {
            event.preventDefault();
            openConversation(startForm.action, {
                method: startForm.method.toUpperCase(),
                body: new FormData(startForm),
            });
        });
    });

    localizeRenderedTimes();
    window.clearInterval(chatPresenceTimer);
    setPresence(presence?.dataset.lastSeenAt ?? '');

    if (presenceUrl) {
        refreshPresence();
        chatPresenceTimer = window.setInterval(refreshPresence, 30000);
    }

    scrollToLatestMessage();

    if (conversationId && window.Echo) {
        const channel = window.Echo.private(`chat.conversation.${conversationId}`);
        channel.listen('.message.sent', ({ message }) => handleConversationMessage(message));
        channel.listen('.messages.read', ({ conversation_id: readConversationId, reader_id: readerId, message_ids: messageIds, read_at: readAt }) => {
            if (
                String(readConversationId) !== String(conversationId)
                || Number(readerId) === currentUserId
                || !Array.isArray(messageIds)
            ) {
                return;
            }

            messageIds.forEach((messageId) => setMessageSeen(messageId, readAt));
        });
        channel.subscribed?.(() => setLiveStatus('Live updates are on', 'ready'));
        channel.error?.(() => setLiveStatus('Live updates are unavailable', 'error'));
    } else if (conversationId) {
        setLiveStatus('Live updates are unavailable', 'error');
    } else {
        setLiveStatus('Choose a conversation to start a chat', 'ready');
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const body = input?.value.trim() ?? '';

        if (!body || !sendUrl) {
            setComposerStatus('Please write a message first.', true);
            input?.focus();
            return;
        }

        if (body.length > 2000) {
            setComposerStatus('Messages may not be longer than 2,000 characters.', true);
            input?.focus();
            return;
        }

        setComposerStatus();
        if (sendButton) {
            sendButton.disabled = true;
            sendButton.classList.add('is-sending');
        }

        try {
            const response = await window.axios.post(sendUrl, { body }, {
                headers: { Accept: 'application/json' },
            });
            const message = response.data.message;

            createMessage(message);
            syncConversationContact(message);
            input.value = '';
            input.style.height = '';
            setComposerStatus('Message sent.');
            input.focus();
        } catch (error) {
            const message = error.response?.data?.errors?.body?.[0]
                ?? error.response?.data?.message
                ?? 'Your message could not be sent. Please try again.';
            setComposerStatus(message, true);
        } finally {
            if (sendButton) {
                sendButton.disabled = false;
                sendButton.classList.remove('is-sending');
            }
        }
    });

    input?.addEventListener('keydown', (event) => {
        if (
            event.key !== 'Enter'
            || event.shiftKey
            || event.isComposing
            || event.keyCode === 229
        ) {
            return;
        }

        event.preventDefault();

        if (!sendButton?.disabled) {
            form?.requestSubmit();
        }
    });

    input?.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    });

    window.addEventListener('chat:inbox-message', (event) => {
        const { message, incrementUnread = false } = event.detail ?? {};
        syncConversationContact(message, incrementUnread);
    });

    window.addEventListener('chat:job-application', (event) => {
        createApplicationConversation(event.detail?.application);
    });

    document.addEventListener('visibilitychange', () => {
        if (chat.isConnected && isPageVisible()) {
            markConversationRead();
            scrollToLatestMessage();
            refreshPresence();
        }
    });

    markConversationRead();
};
