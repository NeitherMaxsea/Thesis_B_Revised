const rememberId = (ids, id) => {
    const key = String(id ?? '');

    if (!key || ids.has(key)) {
        return false;
    }

    ids.add(key);

    if (ids.size > 500) {
        ids.delete(ids.values().next().value);
    }

    return true;
};

const sameOriginUrl = (value, fallback = '') => {
    try {
        const url = new URL(value || fallback, window.location.origin);
        return url.origin === window.location.origin ? url.href : fallback;
    } catch {
        return fallback;
    }
};

export const initRealtimeInbox = ({ showSweetToast } = {}) => {
    const body = document.body;
    const currentUserId = Number(body.dataset.authUserId);

    if (!currentUserId || body.dataset.realtimeInboxInitialized === 'true') {
        return;
    }

    body.dataset.realtimeInboxInitialized = 'true';

    const processedMessageIds = new Set();
    const processedApplicationIds = new Set();
    let unreadMessageCount = Math.max(0, Number(body.dataset.unreadMessageCount) || 0);
    let liveApplicationCount = Math.max(
        0,
        Number(document.querySelector('[data-job-application-badge]')?.dataset.count) || 0,
    );

    const updateUnreadMessageBadges = () => {
        body.dataset.unreadMessageCount = String(unreadMessageCount);

        document.querySelectorAll('[data-unread-message-badge]').forEach((badge) => {
            badge.textContent = unreadMessageCount > 99 ? '99+' : String(unreadMessageCount);
            badge.hidden = unreadMessageCount === 0;
        });
    };

    const updateApplicationBadges = () => {
        document.querySelectorAll('[data-job-application-badge]').forEach((badge) => {
            badge.dataset.count = String(liveApplicationCount);
            badge.textContent = liveApplicationCount > 99 ? '99+' : String(liveApplicationCount);
            badge.hidden = liveApplicationCount === 0;
        });
    };

    const messagesUrl = () => document.querySelector('[data-messages-link]')?.href
        ?? `${window.location.origin}/messages`;

    const hasApplicationItem = (applicationId) => [...document.querySelectorAll('[data-job-application-id]')]
        .some((item) => item.dataset.jobApplicationId === String(applicationId));

    const prependApplicationNotification = (application) => {
        const list = document.querySelector('[data-job-application-list]');

        if (!list || hasApplicationItem(application.id)) {
            return;
        }

        list.querySelector('[data-job-application-empty]')?.remove();

        const item = document.createElement('a');
        item.className = 'dashboard-notification-item';
        item.dataset.jobApplicationId = String(application.id);
        item.href = sameOriginUrl(application.conversation_url, messagesUrl());

        const dot = document.createElement('span');
        dot.className = 'dashboard-notification-dot';
        dot.setAttribute('aria-hidden', 'true');

        const copy = document.createElement('span');
        const title = document.createElement('strong');
        title.textContent = `${application.applicant?.name ?? 'An applicant'} applied`;
        const detail = document.createElement('small');
        detail.textContent = `${application.job_title ?? 'Job posting'} · Just now`;
        copy.append(title, detail);
        item.append(dot, copy);
        list.prepend(item);
    };

    const incrementEmployerApplicationCount = () => {
        document.querySelectorAll('[data-employer-application-count]').forEach((count) => {
            const current = Number(count.textContent.replace(/[^0-9]/g, '')) || 0;
            count.textContent = String(current + 1);
        });
    };

    updateUnreadMessageBadges();
    updateApplicationBadges();

    window.addEventListener('chat:conversation-read', (event) => {
        const clearedCount = Math.max(0, Number(event.detail?.clearedCount) || 0);
        unreadMessageCount = Math.max(0, unreadMessageCount - clearedCount);
        updateUnreadMessageBadges();
    });

    window.addEventListener('notifications:job-applications-cleared', () => {
        liveApplicationCount = 0;
        updateApplicationBadges();
    });

    if (!window.Echo) {
        return;
    }

    window.Echo.private(`App.Models.User.${currentUserId}`)
        .listen('.message.sent', ({ message }) => {
            if (
                !message?.id
                || Number(message.sender?.id) === currentUserId
                || !rememberId(processedMessageIds, message.id)
            ) {
                return;
            }

            const activeChat = document.querySelector('[data-applicant-chat]');
            const isActiveConversation = activeChat?.dataset.conversationId === String(message.conversation_id);
            const incrementUnread = !(isActiveConversation && document.visibilityState !== 'hidden');

            if (incrementUnread) {
                unreadMessageCount += 1;
                updateUnreadMessageBadges();
            }

            window.dispatchEvent(new CustomEvent('chat:inbox-message', {
                detail: { message, incrementUnread },
            }));
        })
        .listen('.job-application.submitted', ({ application }) => {
            if (
                body.dataset.accountType !== 'employer'
                || !application?.id
                || hasApplicationItem(application.id)
                || !rememberId(processedApplicationIds, application.id)
            ) {
                return;
            }

            prependApplicationNotification(application);
            liveApplicationCount += 1;
            updateApplicationBadges();
            incrementEmployerApplicationCount();

            showSweetToast?.({
                title: 'New job application',
                text: `${application.applicant?.name ?? 'An applicant'} applied for ${application.job_title ?? 'your job posting'}.`,
                icon: 'info',
            });

            window.dispatchEvent(new CustomEvent('chat:job-application', {
                detail: { application },
            }));
        });
};
