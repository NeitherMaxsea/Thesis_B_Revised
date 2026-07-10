// Applicant support chat: messages are persisted by Laravel, then received through
// Echo on a participant-only Reverb channel. No messages are stored in the browser.
export const initApplicantChat = () => {
    const chat = document.querySelector('[data-applicant-chat]');

    if (!chat) {
        return;
    }

    const currentUserId = Number(chat.dataset.currentUserId);
    const conversationId = chat.dataset.conversationId;
    const sendUrl = chat.dataset.sendUrl;
    const messages = chat.querySelector('[data-chat-messages]');
    const form = chat.querySelector('[data-chat-send-form]');
    const input = chat.querySelector('[data-chat-message-input]');
    const sendButton = chat.querySelector('[data-chat-send-button]');
    const liveStatus = chat.querySelector('[data-chat-live-status]');
    const composerStatus = chat.querySelector('[data-chat-composer-status]');
    const search = chat.querySelector('[data-chat-search]');
    const contacts = [...chat.querySelectorAll('[data-chat-contact]')];

    const setLiveStatus = (message, state = 'ready') => {
        if (!liveStatus) {
            return;
        }

        liveStatus.classList.remove('is-ready', 'is-error');
        liveStatus.classList.add(`is-${state}`);
        liveStatus.lastChild.textContent = ` ${message}`;
    };

    const setComposerStatus = (message = '', isError = false) => {
        if (!composerStatus) {
            return;
        }

        composerStatus.textContent = message;
        composerStatus.classList.toggle('is-error', isError);
    };

    const scrollToLatestMessage = () => {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    };

    const formatTime = (isoTime) => {
        if (!isoTime) {
            return '';
        }

        return new Intl.DateTimeFormat(undefined, {
            hour: 'numeric',
            minute: '2-digit',
        }).format(new Date(isoTime));
    };

    const createMessage = (message) => {
        if (!messages || messages.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        messages.querySelector('[data-chat-empty]')?.remove();

        const isMine = Number(message.sender.id) === currentUserId;
        const row = document.createElement('div');
        row.className = `applicant-chat__message${isMine ? ' is-mine' : ''}`;
        row.dataset.messageId = String(message.id);

        if (!isMine) {
            const avatar = document.createElement('span');
            avatar.className = 'applicant-chat__message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            avatar.textContent = message.sender.initials;
            row.append(avatar);
        }

        const content = document.createElement('div');

        if (!isMine) {
            const sender = document.createElement('strong');
            sender.textContent = message.sender.name;
            content.append(sender);
        }

        const body = document.createElement('p');
        body.textContent = message.body;
        const time = document.createElement('time');
        time.dateTime = message.sent_at ?? '';
        time.textContent = formatTime(message.sent_at);
        content.append(body, time);
        row.append(content);
        messages.append(row);
        scrollToLatestMessage();
    };

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();

        contacts.forEach((contact) => {
            contact.hidden = query !== '' && !contact.dataset.chatSearchText.includes(query);
        });
    });

    scrollToLatestMessage();

    if (conversationId && window.Echo) {
        const channel = window.Echo.private(`chat.conversation.${conversationId}`);
        channel.listen('.message.sent', ({ message }) => createMessage(message));
        channel.subscribed?.(() => setLiveStatus('Live updates are on', 'ready'));
        channel.error?.(() => setLiveStatus('Live updates are unavailable', 'error'));
    } else if (conversationId) {
        setLiveStatus('Live updates are unavailable', 'error');
    } else {
        setLiveStatus('Choose support to start a chat', 'ready');
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const body = input?.value.trim() ?? '';

        if (!body || !sendUrl) {
            setComposerStatus('Please write a message first.', true);
            input?.focus();
            return;
        }

        setComposerStatus();
        sendButton.disabled = true;
        sendButton.classList.add('is-sending');

        try {
            const response = await window.axios.post(sendUrl, { body }, {
                headers: { Accept: 'application/json' },
            });

            createMessage(response.data.message);
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
            sendButton.disabled = false;
            sendButton.classList.remove('is-sending');
        }
    });

    input?.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    });
};
