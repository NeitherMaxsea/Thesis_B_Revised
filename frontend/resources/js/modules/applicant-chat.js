import Swal from 'sweetalert2';

let chatPresenceTimer;
let chatUpdatesTimer;
let chatReactionPicker;
let activeChatCleanup = () => {};

export const destroyApplicantChat = () => {
    activeChatCleanup();
    activeChatCleanup = () => {};
};

// Secure one-to-one chat. Laravel persists messages first; Echo only mirrors the
// persisted payload into the existing interface.
export const initApplicantChat = () => {
    const chat = document.querySelector('[data-applicant-chat]');

    if (!chat || chat.dataset.chatInitialized === 'true') {
        return;
    }

    chat.dataset.chatInitialized = 'true';

    const currentUserId = Number(chat.dataset.currentUserId);
    const currentAccountType = chat.dataset.accountType;
    const conversationId = chat.dataset.conversationId;
    const sendUrl = chat.dataset.sendUrl;
    const markReadUrl = chat.dataset.markReadUrl;
    const updatesUrl = chat.dataset.updatesUrl;
    const typingUrl = chat.dataset.typingUrl;
    const archiveUrl = chat.dataset.archiveUrl;
    const deleteUrl = chat.dataset.deleteUrl;
    const muteUrl = chat.dataset.muteUrl;
    const reactionUrlTemplate = chat.dataset.reactionUrlTemplate;
    const requirementsAcknowledgeUrlTemplate = chat.dataset.requirementsAcknowledgeUrlTemplate;
    const hiringActionsUrl = chat.dataset.hiringActionsUrl;
    const messagesIndexUrl = chat.dataset.messagesIndexUrl;
    const messages = chat.querySelector('[data-chat-messages]');
    const liveStatus = chat.querySelector('[data-chat-live-status]');
    const composerStatus = chat.querySelector('[data-chat-composer-status]');
    const search = chat.querySelector('[data-chat-search]');
    const conversationList = chat.querySelector('[data-chat-conversation-list]');
    const conversationPanel = chat.querySelector('.applicant-chat__conversation');
    const conversationLoader = chat.querySelector('[data-chat-conversation-loader]');
    const conversationLoaderMessage = chat.querySelector('[data-chat-conversation-loader-message]');
    const presence = chat.querySelector('[data-chat-presence]');
    const typing = chat.querySelector('[data-chat-typing]');
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

        const attachmentPreview = document.createElement('div');
        attachmentPreview.className = 'applicant-chat__attachment-preview';
        attachmentPreview.dataset.chatAttachmentPreview = '';
        attachmentPreview.hidden = true;

        const attachmentLabel = document.createElement('label');
        attachmentLabel.className = 'applicant-chat__attach-button';
        attachmentLabel.title = 'Attach image or video';
        const attachmentLabelText = document.createElement('span');
        attachmentLabelText.className = 'sr-only';
        attachmentLabelText.textContent = 'Attach image or video';
        const attachmentInput = document.createElement('input');
        attachmentInput.type = 'file';
        attachmentInput.name = 'attachment';
        attachmentInput.accept = 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime';
        attachmentInput.dataset.chatAttachmentInput = '';
        const attachmentIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        attachmentIcon.setAttribute('viewBox', '0 0 24 24');
        attachmentIcon.setAttribute('aria-hidden', 'true');
        attachmentIcon.innerHTML = '<path d="m21.4 11.6-8.7 8.7a6 6 0 0 1-8.5-8.5l9.4-9.4a4.1 4.1 0 0 1 5.8 5.8L10 17.6a2.2 2.2 0 0 1-3.1-3.1l8.7-8.7"></path>';
        attachmentLabel.append(attachmentLabelText, attachmentInput, attachmentIcon);

        const input = document.createElement('textarea');
        input.id = 'chat-message-body';
        input.name = 'body';
        input.rows = 1;
        input.maxLength = 2000;
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

        composer.append(label, attachmentPreview, attachmentLabel, input, button, status);
        conversationPanel.append(composer);
    };

    ensureComposer();

    const form = chat.querySelector('[data-chat-send-form]');
    const input = chat.querySelector('[data-chat-message-input]');
    const attachmentInput = chat.querySelector('[data-chat-attachment-input]');
    const attachmentPreview = chat.querySelector('[data-chat-attachment-preview]');
    const sendButton = chat.querySelector('[data-chat-send-button]');
    const activeComposerStatus = chat.querySelector('[data-chat-composer-status]');
    const settingsStatus = chat.querySelector('[data-chat-settings-status]');
    const hiringTimeline = chat.querySelector('[data-hiring-timeline]');
    const hiringStageForm = chat.querySelector('[data-hiring-stage-form]');
    const hiringStageSelect = chat.querySelector('[data-hiring-stage-select]');
    const hiringStageSubmit = chat.querySelector('[data-hiring-stage-submit]');
    const hiringStageStatus = chat.querySelector('[data-hiring-stage-status]');
    const hiringActions = chat.querySelector('[data-hiring-actions]');
    const hiringActionDialog = chat.querySelector('[data-hiring-action-dialog]');
    const hiringActionForm = chat.querySelector('[data-hiring-action-form]');
    const hiringActionInput = chat.querySelector('[data-hiring-action-input]');
    const hiringActionTitle = chat.querySelector('[data-hiring-action-title]');
    const hiringActionDescription = chat.querySelector('[data-hiring-action-description]');
    const hiringActionStatus = chat.querySelector('[data-hiring-action-status]');
    const hiringActionSubmit = chat.querySelector('[data-hiring-action-submit]');
    const reactionEmojis = ['👍', '❤️', '😊'];

    let readInFlight = false;
    let readQueued = false;
    let conversationNavigationInProgress = false;
    let updatesInFlight = false;
    let typingRequestInFlight = false;
    let lastTypingRequestAt = 0;
    let typingClearTimer;
    let conversationChannel;
    let latestMessageId = Math.max(0, ...[...(messages?.querySelectorAll('[data-message-id]') ?? [])]
        .map((message) => Number(message.dataset.messageId) || 0));

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

    let attachmentPreviewUrl = '';

    const clearAttachmentPreview = ({ clearInput = false } = {}) => {
        if (attachmentPreviewUrl) {
            URL.revokeObjectURL(attachmentPreviewUrl);
            attachmentPreviewUrl = '';
        }

        if (attachmentPreview) {
            attachmentPreview.replaceChildren();
            attachmentPreview.hidden = true;
        }

        conversationPanel?.classList.remove('has-attachment-preview');

        if (clearInput && attachmentInput) {
            attachmentInput.value = '';
        }
    };

    const showAttachmentPreview = (attachment) => {
        if (!attachmentPreview || !attachment) {
            return;
        }

        clearAttachmentPreview();
        attachmentPreviewUrl = URL.createObjectURL(attachment);

        const media = attachment.type.startsWith('image/')
            ? document.createElement('img')
            : document.createElement('video');

        media.src = attachmentPreviewUrl;
        media.className = 'applicant-chat__attachment-preview-media';

        if (media instanceof HTMLImageElement) {
            media.alt = `Preview of ${attachment.name}`;
        } else {
            media.muted = true;
            media.preload = 'metadata';
            media.controls = true;
            media.setAttribute('aria-label', `Preview of ${attachment.name}`);
        }

        const details = document.createElement('span');
        details.className = 'applicant-chat__attachment-preview-copy';
        const name = document.createElement('strong');
        name.textContent = attachment.name;
        const size = document.createElement('small');
        size.textContent = `${Math.max(1, Math.ceil(attachment.size / 1024))} KB · Ready to send`;
        details.append(name, size);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'applicant-chat__attachment-preview-remove';
        remove.setAttribute('aria-label', 'Remove attachment');
        remove.textContent = '×';
        remove.addEventListener('click', () => {
            clearAttachmentPreview({ clearInput: true });
            setComposerStatus('Attachment removed.');
            input?.focus();
        });

        attachmentPreview.append(media, details, remove);
        attachmentPreview.hidden = false;
        conversationPanel?.classList.add('has-attachment-preview');
    };

    const setSettingsStatus = (mutedUntil = '') => {
        if (!settingsStatus) {
            return;
        }

        const mutedDate = parseDate(mutedUntil);
        settingsStatus.textContent = mutedDate && mutedDate > new Date()
            ? 'Notifications muted'
            : 'Notifications on';
    };

    const setHiringStageStatus = (message = '', isError = false) => {
        if (!hiringStageStatus) {
            return;
        }

        hiringStageStatus.textContent = message;
        hiringStageStatus.classList.toggle('is-error', isError);
    };

    const hiringActionCopy = {
        interview: {
            title: 'Mag-schedule ng interview',
            description: 'Ilagay ang petsa, oras, at paraan bago ipadala sa applicant.',
        },
        assessment: {
            title: 'Magpadala ng skills assessment',
            description: 'Magbigay ng assessment invitation at malinaw na panuto.',
        },
        documents: {
            title: 'Humingi ng documents',
            description: 'Gumawa ng document checklist na matatanggap ng applicant.',
        },
    };

    const setHiringActionStatus = (message = '', isError = false) => {
        if (!hiringActionStatus) {
            return;
        }

        hiringActionStatus.textContent = message;
        hiringActionStatus.classList.toggle('is-error', isError);
    };

    const selectHiringAction = (action) => {
        if (!hiringActionForm || !hiringActionInput || !hiringActionCopy[action]) {
            return;
        }

        hiringActionInput.value = action;
        hiringActionTitle.textContent = hiringActionCopy[action].title;
        hiringActionDescription.textContent = hiringActionCopy[action].description;
        hiringActionForm.querySelectorAll('[data-hiring-action-fields]').forEach((fields) => {
            const isActive = fields.dataset.hiringActionFields === action;
            fields.hidden = !isActive;
            fields.querySelectorAll('input, textarea, select').forEach((field) => {
                field.disabled = !isActive;
            });
        });
    };

    chat.querySelectorAll('[data-hiring-action-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.hiringActionOpen;
            selectHiringAction(action);
            hiringActions?.removeAttribute('open');
            setHiringActionStatus();
            hiringActionDialog?.showModal();
            hiringActionDialog?.querySelector('[data-hiring-action-fields]:not([hidden]) input, [data-hiring-action-fields]:not([hidden]) textarea, [data-hiring-action-fields]:not([hidden]) select')?.focus();
        });
    });

    chat.querySelectorAll('[data-hiring-action-close]').forEach((button) => {
        button.addEventListener('click', () => {
            hiringActionDialog?.close();
        });
    });

    hiringActionDialog?.addEventListener('click', (event) => {
        if (event.target === hiringActionDialog) {
            hiringActionDialog.close();
        }
    });

    hiringActionForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!hiringActionsUrl || !hiringActionForm.checkValidity()) {
            hiringActionForm.reportValidity();
            return;
        }

        setHiringActionStatus();
        const originalText = hiringActionSubmit?.textContent ?? 'Ipadala sa applicant';
        if (hiringActionSubmit) {
            hiringActionSubmit.disabled = true;
            hiringActionSubmit.textContent = 'Ipinapadala…';
        }

        try {
            const response = await window.axios.post(hiringActionsUrl, new FormData(hiringActionForm), {
                headers: { Accept: 'application/json' },
            });
            createMessage(response.data.message);
            syncConversationContact(response.data.message);
            updateHiringTimeline(response.data.application_timeline);
            setComposerStatus('Naipadala ang hiring card sa applicant.');
            hiringActionDialog?.close();
            hiringActionForm.reset();
            selectHiringAction('interview');
        } catch (error) {
            const errors = error.response?.data?.errors;
            const message = errors
                ? Object.values(errors).flat()[0]
                : (error.response?.data?.message ?? 'Hindi naipadala ang hiring card. Subukan muli.');
            setHiringActionStatus(message, true);
        } finally {
            if (hiringActionSubmit) {
                hiringActionSubmit.disabled = false;
                hiringActionSubmit.textContent = originalText;
            }
        }
    });

    const updateHiringTimeline = (timeline) => {
        if (!hiringTimeline || !timeline || !Number.isInteger(Number(timeline.number))) {
            return;
        }

        const currentStage = Number(timeline.number);
        hiringTimeline.dataset.currentStage = String(currentStage);

        const title = hiringTimeline.querySelector('[data-hiring-stage-title]');
        const progress = hiringTimeline.querySelector('[data-hiring-stage-progress]');
        const stages = [...hiringTimeline.querySelectorAll('[data-hiring-stage]')];
        const stagesTrack = hiringTimeline.querySelector('.applicant-chat__hiring-stages');

        if (title && timeline.title) {
            title.textContent = timeline.title;
        }

        if (progress) {
            progress.textContent = `Step ${currentStage} of ${stages.length}`;
        }

        if (stagesTrack) {
            const completedProgress = Math.max(0, currentStage - 1) / Math.max(1, stages.length - 1);
            stagesTrack.style.setProperty('--hiring-progress', `${completedProgress * 80}%`);
        }

        stages.forEach((stage) => {
            const stageNumber = Number(stage.dataset.stageNumber);
            const isCurrent = stageNumber === currentStage;
            const isCompleted = stageNumber < currentStage;
            const state = stage.querySelector('[data-hiring-stage-state]');

            stage.classList.toggle('is-current', isCurrent);
            stage.classList.toggle('is-completed', isCompleted);

            if (isCurrent) {
                stage.setAttribute('aria-current', 'step');
            } else {
                stage.removeAttribute('aria-current');
            }

            if (state) {
                state.textContent = isCurrent
                    ? 'Current stage'
                    : (isCompleted ? 'Completed stage' : 'Upcoming stage');
            }
        });

        if (hiringStageSelect && timeline.status) {
            hiringStageSelect.value = timeline.status;
        }
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

    const setTyping = (isTyping, senderId) => {
        if (!typing || Number(senderId) === currentUserId) {
            return;
        }

        window.clearTimeout(typingClearTimer);
        typing.hidden = !isTyping;

        if (isTyping) {
            typingClearTimer = window.setTimeout(() => setTyping(false, senderId), 5500);
        }
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

    const contactContext = (accountType) => {
        if (accountType === 'admin') {
            return 'Platform Support';
        }

        return accountType === 'employer' ? 'Employer' : 'PWD Applicant';
    };

    const renderAvatar = (avatar, user = {}) => {
        if (!avatar) return;

        avatar.dataset.chatAvatarUserId = String(user.id ?? '');
        avatar.classList.toggle('has-image', Boolean(user.photo_url));

        if (user.photo_url) {
            const image = document.createElement('img');
            image.src = user.photo_url;
            image.alt = '';
            avatar.replaceChildren(image);
            return;
        }

        avatar.textContent = user.initials ?? '';
    };

    const createInboxConversationContact = (message) => {
        const targetConversationId = String(message?.conversation_id ?? '');

        if (!conversationList || !targetConversationId || !message?.sender) {
            return null;
        }

        const href = safeConversationUrl('', targetConversationId);

        if (!href) {
            return null;
        }

        conversationList.querySelector('[data-chat-empty-contacts]')?.remove();
        ensureConversationsLabel();

        const contact = document.createElement('a');
        contact.href = href;
        contact.className = 'applicant-chat__contact';
        contact.dataset.chatContact = '';
        contact.dataset.conversationId = targetConversationId;
        contact.dataset.unreadCount = '0';
        contact.dataset.chatLastMessageId = String(message.id ?? 0);

        const avatar = document.createElement('span');
        avatar.className = 'applicant-chat__avatar';
        avatar.setAttribute('aria-hidden', 'true');
        renderAvatar(avatar, message.sender);

        const copy = document.createElement('span');
        copy.className = 'applicant-chat__contact-copy';
        const name = document.createElement('strong');
        name.dataset.chatContactName = '';
        name.textContent = message.sender.name ?? 'New conversation';
        const context = document.createElement('small');
        context.dataset.chatContactContext = '';
        context.textContent = contactContext(message.sender.account_type);
        const preview = document.createElement('em');
        preview.dataset.chatContactPreview = '';
        preview.textContent = messagePreview(message);
        copy.append(name, context, preview);

        const metadata = document.createElement('span');
        metadata.className = 'applicant-chat__contact-meta';
        const time = document.createElement('time');
        time.dataset.chatContactTime = '';
        time.dateTime = message.sent_at ?? '';
        time.textContent = formatContactTime(message.sent_at);
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

        getContacts()
            .find((candidate) => candidate.dataset.recipientId === String(message.sender.id))
            ?.closest('form')
            ?.remove();

        return contact;
    };

    const syncConversationContact = (message, incrementUnread = false) => {
        const targetConversationId = String(message?.conversation_id ?? '');

        if (!targetConversationId) {
            return;
        }

        let contact = findConversationContact(targetConversationId);

        if (!contact) {
            contact = createInboxConversationContact(message);

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
        }

        const preview = contact.querySelector('[data-chat-contact-preview]');
        const time = contact.querySelector('[data-chat-contact-time]');

        if (preview) {
            preview.textContent = messagePreview(message);
        }

        if (time) {
            time.dateTime = message.sent_at ?? '';
            time.textContent = formatContactTime(message.sent_at);
            time.hidden = !time.textContent;
        }

        contact.dataset.chatLastMessageId = String(message.id ?? 0);

        if (incrementUnread) {
            setContactUnread(contact, (Number(contact.dataset.unreadCount) || 0) + 1);
        }

        updateContactSearchText(contact);
        moveContactToTop(contact);
    };

    const updateReactionControls = (controls, reactions = []) => {
        controls.replaceChildren();

        (Array.isArray(reactions) ? reactions : []).forEach((reaction) => {
            const count = Math.max(0, Number(reaction?.count) || 0);

            if (!reaction?.emoji || count === 0) {
                return;
            }

            const chip = document.createElement('span');
            chip.className = 'applicant-chat__reaction-chip';
            chip.classList.toggle(
                'is-active',
                Array.isArray(reaction.user_ids) && reaction.user_ids.map(Number).includes(currentUserId),
            );
            const emoji = document.createElement('span');
            emoji.textContent = reaction.emoji;
            const countElement = document.createElement('b');
            countElement.textContent = String(count);
            chip.append(emoji, countElement);
            controls.append(chip);
        });

        controls.hidden = controls.childElementCount === 0;
    };

    const createReactionControls = (reactions = []) => {
        const controls = document.createElement('div');
        controls.className = 'applicant-chat__message-reactions';
        controls.dataset.messageReactions = '';

        updateReactionControls(controls, reactions);

        return controls;
    };

    const setMessageReactions = (messageId, reactions = []) => {
        const row = messages?.querySelector(`[data-message-id="${Number(messageId)}"]`);
        const controls = row?.querySelector('[data-message-reactions]');

        if (controls) {
            updateReactionControls(controls, reactions);
        }
    };

    const reactionUrl = (messageId) => reactionUrlTemplate?.replace(
        '__message__',
        encodeURIComponent(String(messageId)),
    );

    const requirementsAcknowledgeUrl = (messageId) => requirementsAcknowledgeUrlTemplate?.replace(
        '__message__',
        encodeURIComponent(String(messageId)),
    );

    const messagePreview = (message) => {
        if (message?.message_type === 'requirements_card') {
            return 'May ipinadalang requirement card';
        }

        if (typeof message?.body === 'string' && message.body.trim() !== '') {
            return message.body;
        }

        return message?.attachment?.mime?.startsWith('image/') ? 'Photo' : 'Video';
    };

    const createAttachmentPreview = (attachment) => {
        if (!attachment?.url || !attachment?.mime) {
            return null;
        }

        const media = document.createElement('div');
        media.className = 'applicant-chat__message-media';

        if (attachment.mime.startsWith('image/')) {
            const image = document.createElement('img');
            image.src = attachment.url;
            image.alt = attachment.name || 'Attached image';
            image.loading = 'lazy';
            media.append(image);
        } else if (attachment.mime.startsWith('video/')) {
            const video = document.createElement('video');
            video.controls = true;
            video.preload = 'metadata';
            const source = document.createElement('source');
            source.src = attachment.url;
            source.type = attachment.mime;
            video.append(source);
            media.append(video);
        } else {
            return null;
        }

        if (attachment.name) {
            const name = document.createElement('small');
            name.textContent = attachment.name;
            media.append(name);
        }

        return media;
    };

    const requirementCheckIcon = () => {
        const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<path d="m5 12.5 4.2 4L19 6.8"></path>';
        return icon;
    };

    const createRequirementsCard = (message, isMine) => {
        if (message?.message_type !== 'requirements_card') {
            return null;
        }

        const metadata = message.metadata && typeof message.metadata === 'object'
            ? message.metadata
            : {};
        const card = document.createElement('section');
        card.className = 'applicant-chat__requirements-card';
        card.dataset.requirementsCard = '';
        card.dataset.messageId = String(message.id);
        card.setAttribute('aria-label', 'Mga requirement para sa aplikasyon');

        const header = document.createElement('header');
        const iconWrap = document.createElement('span');
        iconWrap.className = 'applicant-chat__requirements-icon';
        iconWrap.setAttribute('aria-hidden', 'true');
        iconWrap.innerHTML = '<svg viewBox="0 0 24 24"><path d="M9 4.5h6M9 3h6a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 15 7H9a1.5 1.5 0 0 1-1.5-1.5v-1A1.5 1.5 0 0 1 9 3Z"></path><path d="M7 5.5H5.75A1.75 1.75 0 0 0 4 7.25v12A1.75 1.75 0 0 0 5.75 21h12a1.75 1.75 0 0 0 1.75-1.75v-12A1.75 1.75 0 0 0 17.75 5.5H16"></path><path d="m8 13 2 2 5-5"></path></svg>';
        const heading = document.createElement('span');
        const eyebrow = document.createElement('p');
        eyebrow.textContent = 'Checklist ng kailangan';
        const title = document.createElement('h3');
        title.textContent = metadata.heading || 'Mga kailangang ihanda';
        heading.append(eyebrow, title);
        header.append(iconWrap, heading);

        const acknowledged = Boolean(metadata.acknowledged_at);
        if (acknowledged) {
            const badge = document.createElement('b');
            badge.className = 'is-confirmed';
            badge.textContent = 'Nabasa na';
            header.append(badge);
        }

        card.append(header);

        if (metadata.job_title) {
            const job = document.createElement('p');
            job.className = 'applicant-chat__requirements-job';
            job.textContent = `Para sa: ${metadata.job_title}`;
            card.append(job);
        }

        const intro = document.createElement('p');
        intro.className = 'applicant-chat__requirements-intro';
        intro.textContent = metadata.intro || 'Pakihanda ang mga sumusunod para sa iyong aplikasyon.';
        card.append(intro);

        const list = document.createElement('ul');
        list.className = 'applicant-chat__requirements-list';
        const requirements = Array.isArray(metadata.items) ? metadata.items : [];
        const items = requirements.filter((item) => item && typeof item.label === 'string' && item.label.trim() !== '');

        (items.length > 0 ? items : [{
            label: 'Hintayin ang susunod na mensahe ng employer.',
            status: 'Wala pang dagdag na dokumento',
        }]).forEach((item) => {
            const row = document.createElement('li');
            const check = document.createElement('span');
            check.setAttribute('aria-hidden', 'true');
            check.append(requirementCheckIcon());
            const copy = document.createElement('div');
            const label = document.createElement('strong');
            label.textContent = item.label;
            const status = document.createElement('small');
            status.textContent = item.status || 'Kailangang ihanda';
            copy.append(label, status);
            row.append(check, copy);
            list.append(row);
        });
        card.append(list);

        if (!isMine && currentAccountType === 'pwd_applicant' && !acknowledged) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'applicant-chat__requirements-confirm';
            button.dataset.requirementsAcknowledge = '';
            button.dataset.requirementsAcknowledgeUrl = requirementsAcknowledgeUrl(message.id) || '';
            button.textContent = 'Nabasa ko, ihahanda ko ito';
            card.append(button);
        } else if (acknowledged) {
            const confirmed = document.createElement('p');
            confirmed.className = 'applicant-chat__requirements-confirmed';
            confirmed.textContent = 'Nabasa na ng applicant at ihahanda niya ang mga requirement.';
            card.append(confirmed);
        }

        return card;
    };

    const createHiringActionCard = (message) => {
        if (message?.message_type !== 'hiring_action') {
            return null;
        }

        const metadata = message.metadata && typeof message.metadata === 'object'
            ? message.metadata
            : {};
        const card = document.createElement('section');
        card.className = `applicant-chat__hiring-card applicant-chat__hiring-card--${metadata.action || 'notice'}`;
        card.setAttribute('aria-label', metadata.title || 'Hiring update');

        const header = document.createElement('header');
        const icon = document.createElement('span');
        icon.className = 'applicant-chat__hiring-card-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = metadata.action === 'documents'
            ? '<svg viewBox="0 0 24 24"><path d="M7 3.5h7l3 3V20.5H7z"></path><path d="M14 3.5v4h3M9.5 12h5M9.5 15.5h5"></path></svg>'
            : (metadata.action === 'assessment'
                ? '<svg viewBox="0 0 24 24"><path d="M4 5.5h16v13H4z"></path><path d="M8 9h8M8 12h4M8 15h5"></path></svg>'
                : '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M8 3v4M16 3v4M4 10h16M8 14h3M8 17h6"></path></svg>');
        const copy = document.createElement('span');
        const eyebrow = document.createElement('p');
        eyebrow.textContent = metadata.eyebrow || 'Hiring update';
        const title = document.createElement('h3');
        title.textContent = metadata.title || 'May bagong update ang employer';
        copy.append(eyebrow, title);
        header.append(icon, copy);
        card.append(header);

        if (metadata.intro) {
            const intro = document.createElement('p');
            intro.className = 'applicant-chat__hiring-card-intro';
            intro.textContent = metadata.intro;
            card.append(intro);
        }

        const details = Array.isArray(metadata.details) ? metadata.details : [];
        if (details.length > 0) {
            const list = document.createElement('dl');
            list.className = 'applicant-chat__hiring-card-details';
            details.forEach((detail) => {
                if (!detail || !detail.label || !detail.value) {
                    return;
                }
                const row = document.createElement('div');
                const label = document.createElement('dt');
                label.textContent = detail.label;
                const value = document.createElement('dd');
                value.textContent = detail.value;
                row.append(label, value);
                list.append(row);
            });
            if (list.childElementCount > 0) {
                card.append(list);
            }
        }

        const items = Array.isArray(metadata.items) ? metadata.items : [];
        if (items.length > 0) {
            const list = document.createElement('ul');
            list.className = 'applicant-chat__hiring-card-items';
            items.filter((item) => typeof item === 'string' && item.trim() !== '').forEach((item) => {
                const row = document.createElement('li');
                row.textContent = item;
                list.append(row);
            });
            if (list.childElementCount > 0) {
                card.append(list);
            }
        }

        return card;
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
        row.classList.toggle('has-requirements-card', message.message_type === 'requirements_card');
        row.classList.toggle('has-hiring-card', message.message_type === 'hiring_action');
        row.dataset.messageId = String(message.id);
        row.dataset.messageSenderId = String(message.sender.id);

        if (!isMine) {
            const avatar = document.createElement('span');
            avatar.className = 'applicant-chat__message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            renderAvatar(avatar, message.sender);
            row.append(avatar);
        }

        const content = document.createElement('div');

        if (!isMine) {
            const sender = document.createElement('strong');
            sender.textContent = message.sender.name ?? '';
            content.append(sender);
        }

        const attachment = createAttachmentPreview(message.attachment);
        const requirementsCard = createRequirementsCard(message, isMine);
        const hiringActionCard = createHiringActionCard(message);
        const body = document.createElement('p');
        body.textContent = message.body ?? '';
        const time = document.createElement('time');
        time.dataset.chatMessageTime = '';
        time.dateTime = message.sent_at ?? '';
        time.textContent = formatTime(message.sent_at);
        if (attachment) {
            content.append(attachment);
        }

        if (requirementsCard) {
            content.append(requirementsCard);
        } else if (hiringActionCard) {
            content.append(hiringActionCard);
        } else if (body.textContent.trim() !== '') {
            content.append(body);
        }

        content.append(time);

        if (isMine) {
            const status = document.createElement('span');
            const isRead = Boolean(message.is_read || message.read_at);
            status.className = 'applicant-chat__message-status';
            status.dataset.messageStatus = '';
            status.dataset.readAt = message.read_at ?? '';
            status.textContent = isRead ? 'Seen' : 'Sent';
            content.append(status);
        }

        content.append(createReactionControls(message.reactions));

        row.append(content);
        messages.append(row);
        latestMessageId = Math.max(latestMessageId, Number(message.id));
        scrollToLatestMessage();

        return true;
    };

    const replaceRequirementsCard = (message) => {
        const card = createRequirementsCard(
            message,
            Number(message?.sender?.id) === currentUserId,
        );
        const currentCard = messages?.querySelector(
            `[data-requirements-card][data-message-id="${Number(message?.id)}"]`,
        );

        if (card && currentCard) {
            currentCard.replaceWith(card);
        }
    };

    messages?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-requirements-acknowledge]');

        if (!button || button.disabled) {
            return;
        }

        const url = button.dataset.requirementsAcknowledgeUrl;

        if (!url) {
            setComposerStatus('Hindi maipadala ang kumpirmasyon. Subukan muli.', true);
            return;
        }

        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Ikinukumpirma…';

        try {
            const response = await window.axios.post(url, {}, {
                headers: { Accept: 'application/json' },
            });
            const card = response.data.requirements_card;
            const reply = response.data.reply;

            replaceRequirementsCard(card);
            updateHiringTimeline(response.data.application_timeline);

            if (reply) {
                createMessage(reply);
                syncConversationContact(reply);
            }

            setComposerStatus('Naipaalam na sa employer na ihahanda mo ang mga requirement.');
        } catch (error) {
            const message = error.response?.data?.message
                ?? 'Hindi maipadala ang kumpirmasyon. Subukan muli.';
            setComposerStatus(message, true);
            button.disabled = false;
            button.textContent = originalText;
        }
    });

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
            syncConversationUpdates();
        }
    };

    const reactToMessage = async (messageId, emoji) => {
        const url = reactionUrl(messageId);

        if (!url || !reactionEmojis.includes(emoji)) {
            return;
        }

        try {
            const response = await window.axios.post(url, { emoji }, {
                headers: { Accept: 'application/json' },
            });
            const payload = response.data;
            setMessageReactions(payload.message_id, payload.reactions);
            conversationChannel?.whisper('reaction', payload);
        } catch {
            setComposerStatus('Reaction could not be saved. Please try again.', true);
        }
    };

    chatReactionPicker?.remove();
    chatReactionPicker = document.createElement('div');
    chatReactionPicker.className = 'applicant-chat__reaction-picker';
    chatReactionPicker.hidden = true;
    chatReactionPicker.setAttribute('role', 'dialog');
    chatReactionPicker.setAttribute('aria-label', 'Choose a reaction');
    reactionEmojis.forEach((emoji) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.messageReactionPicker = '';
        button.dataset.emoji = emoji;
        button.setAttribute('aria-label', `React ${emoji}`);
        button.textContent = emoji;
        chatReactionPicker.append(button);
    });
    document.body.append(chatReactionPicker);

    let reactionPickerMessageId = null;
    let reactionPickerCloseTimer;

    const closeReactionPicker = () => {
        reactionPickerMessageId = null;
        window.clearTimeout(reactionPickerCloseTimer);
        chatReactionPicker.hidden = true;
    };

    const openReactionPicker = (messageId, target) => {
        if (!messageId || !target) {
            return;
        }

        reactionPickerMessageId = messageId;
        chatReactionPicker.hidden = false;
        const targetBounds = target.getBoundingClientRect();
        const pickerBounds = chatReactionPicker.getBoundingClientRect();
        const left = Math.min(
            Math.max(8, targetBounds.left),
            window.innerWidth - pickerBounds.width - 8,
        );
        const above = targetBounds.top - pickerBounds.height - 8;

        chatReactionPicker.style.left = `${left}px`;
        chatReactionPicker.style.top = `${above >= 8 ? above : targetBounds.bottom + 8}px`;
        window.clearTimeout(reactionPickerCloseTimer);
        reactionPickerCloseTimer = window.setTimeout(closeReactionPicker, 6000);
    };

    chatReactionPicker.addEventListener('click', (event) => {
        const button = event.target.closest('[data-message-reaction-picker]');

        if (!button || !reactionPickerMessageId) {
            return;
        }

        reactToMessage(reactionPickerMessageId, button.dataset.emoji);
        closeReactionPicker();
    });

    const muteConversation = async (duration) => {
        if (!muteUrl) {
            return;
        }

        try {
            const response = await window.axios.patch(muteUrl, { duration }, {
                headers: { Accept: 'application/json' },
            });
            setSettingsStatus(response.data?.muted_until ?? '');
            chat.querySelector('.applicant-chat__actions')?.removeAttribute('open');
        } catch {
            setSettingsStatus();
        }
    };

    const archiveConversation = async () => {
        if (!archiveUrl || !messagesIndexUrl || !window.confirm('Archive this conversation? New messages will restore it.')) {
            return;
        }

        try {
            await window.axios.post(archiveUrl, {}, {
                headers: { Accept: 'application/json' },
            });
            window.Echo?.leave(`chat.conversation.${conversationId}`);
            window.clearInterval(chatPresenceTimer);
            window.clearInterval(chatUpdatesTimer);
            window.location.assign(messagesIndexUrl);
        } catch {
            setSettingsStatus();
        }
    };

    const deleteConversation = async () => {
        if (!deleteUrl || !messagesIndexUrl) {
            return;
        }

        const confirmation = await Swal.fire({
            icon: 'warning',
            title: 'Delete this conversation?',
            text: 'This removes it only from your view. The other participant keeps their history.',
            showCancelButton: true,
            confirmButtonText: 'Delete conversation',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup: 'app-swal-modal',
                title: 'app-swal-modal__title',
                htmlContainer: 'app-swal-modal__message',
                confirmButton: 'app-swal-modal__confirm app-swal-modal__confirm--danger',
                cancelButton: 'app-swal-modal__cancel',
            },
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        try {
            await window.axios.delete(deleteUrl, {
                headers: { Accept: 'application/json' },
            });
            window.Echo?.leave(`chat.conversation.${conversationId}`);
            window.clearInterval(chatPresenceTimer);
            window.clearInterval(chatUpdatesTimer);
            window.location.assign(messagesIndexUrl);
        } catch {
            setSettingsStatus();
        }
    };

    const syncConversationUpdates = async () => {
        if (
            !conversationId
            || !updatesUrl
            || !chat.isConnected
            || !isPageVisible()
            || updatesInFlight
        ) {
            return;
        }

        updatesInFlight = true;

        try {
            const response = await window.axios.get(updatesUrl, {
                params: { after: latestMessageId },
                headers: { Accept: 'application/json' },
            });

            response.data?.messages?.forEach(handleConversationMessage);
            response.data?.read_receipts?.forEach((receipt) => {
                setMessageSeen(receipt.id, receipt.read_at);
            });
            response.data?.message_reactions?.forEach(({ message_id: messageId, reactions }) => {
                setMessageReactions(messageId, reactions);
            });
            setTyping(response.data?.typing?.is_typing === true, response.data?.typing?.user_id);
            updateHiringTimeline(response.data?.application_timeline);
        } catch {
            // The next interval retries. Messaging remains available as soon as
            // either Reverb or this authenticated recovery request responds.
        } finally {
            updatesInFlight = false;
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

            destroyApplicantChat();
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
        renderAvatar(avatar, application.applicant);

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

    hiringStageForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!hiringStageSelect?.value) {
            return;
        }

        setHiringStageStatus();
        hiringStageSelect.disabled = true;
        if (hiringStageSubmit) {
            hiringStageSubmit.disabled = true;
        }

        try {
            const response = await window.axios.patch(hiringStageForm.action, {
                status: hiringStageSelect.value,
            }, {
                headers: { Accept: 'application/json' },
            });
            updateHiringTimeline(response.data?.application_timeline);
            setHiringStageStatus('Hiring stage updated.');
        } catch (error) {
            const message = error.response?.data?.errors?.status?.[0]
                ?? error.response?.data?.message
                ?? 'The hiring stage could not be updated. Please try again.';
            setHiringStageStatus(message, true);
        } finally {
            hiringStageSelect.disabled = false;
            if (hiringStageSubmit) {
                hiringStageSubmit.disabled = false;
            }
        }
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

    chat.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const muteButton = event.target.closest('[data-chat-mute-duration]');

        if (muteButton && chat.contains(muteButton)) {
            event.preventDefault();
            muteConversation(muteButton.dataset.chatMuteDuration);
            return;
        }

        const archiveButton = event.target.closest('[data-chat-archive]');

        if (archiveButton && chat.contains(archiveButton)) {
            event.preventDefault();
            archiveConversation();
            return;
        }

        const deleteButton = event.target.closest('[data-chat-delete]');

        if (deleteButton && chat.contains(deleteButton)) {
            event.preventDefault();
            deleteConversation();
        }
    });

    let longPressTimer;

    const cancelLongPress = () => {
        window.clearTimeout(longPressTimer);
        longPressTimer = undefined;
    };

    const openMessageReactionPicker = (target) => {
        const messageRow = target.closest('[data-message-id]');

        if (!messageRow) {
            return;
        }

        openReactionPicker(
            messageRow.dataset.messageId,
            messageRow.querySelector('p') ?? messageRow,
        );
    };

    chat.addEventListener('pointerdown', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const messageRow = event.target.closest('[data-message-id]');

        if (!messageRow || (event.pointerType === 'mouse' && event.button !== 0)) {
            closeReactionPicker();
            return;
        }

        cancelLongPress();
        longPressTimer = window.setTimeout(() => openMessageReactionPicker(event.target), 450);
    });

    ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
        chat.addEventListener(eventName, cancelLongPress);
    });

    chat.addEventListener('contextmenu', (event) => {
        if (!(event.target instanceof Element) || !event.target.closest('[data-message-id]')) {
            return;
        }

        event.preventDefault();
        cancelLongPress();
        openMessageReactionPicker(event.target);
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

    window.clearInterval(chatUpdatesTimer);

    if (conversationId && updatesUrl) {
        syncConversationUpdates();
        // Echo provides the immediate path. Polling is only a recovery path,
        // so keep it gentle enough that several open users do not overload
        // the application server and make the UI feel slow.
        chatUpdatesTimer = window.setInterval(
            syncConversationUpdates,
            window.Echo ? 8000 : 4000,
        );
    }

    if (conversationId && window.Echo) {
        conversationChannel = window.Echo.private(`chat.conversation.${conversationId}`);
        conversationChannel.listen('.message.sent', ({ message }) => handleConversationMessage(message));
        conversationChannel.listen('.messages.read', ({ conversation_id: readConversationId, reader_id: readerId, message_ids: messageIds, read_at: readAt }) => {
            if (
                String(readConversationId) !== String(conversationId)
                || Number(readerId) === currentUserId
                || !Array.isArray(messageIds)
            ) {
                return;
            }

            messageIds.forEach((messageId) => setMessageSeen(messageId, readAt));
        });
        conversationChannel.listenForWhisper('typing', ({ sender_id: senderId }) => setTyping(true, senderId));
        conversationChannel.listenForWhisper('reaction', ({ message_id: messageId, reactions }) => {
            setMessageReactions(messageId, reactions);
        });
        conversationChannel.subscribed?.(() => setLiveStatus('Live updates are on', 'ready'));
        conversationChannel.error?.(() => setLiveStatus('Live updates are unavailable; syncing automatically', 'error'));
    } else if (conversationId) {
        setLiveStatus('Live updates are unavailable; syncing automatically', 'error');
    } else {
        setLiveStatus('Choose a conversation to start a chat', 'ready');
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const body = input?.value.trim() ?? '';
        const attachment = attachmentInput?.files?.[0] ?? null;

        if ((!body && !attachment) || !sendUrl) {
            setComposerStatus('Write a message or attach an image or video.', true);
            input?.focus();
            return;
        }

        if (body.length > 2000) {
            setComposerStatus('Messages may not be longer than 2,000 characters.', true);
            input?.focus();
            return;
        }

        if (attachment && attachment.size > 25 * 1024 * 1024) {
            setComposerStatus('Attachments may not be larger than 25 MB.', true);
            return;
        }

        const payload = new FormData();

        if (body) {
            payload.append('body', body);
        }

        if (attachment) {
            payload.append('attachment', attachment);
        }

        setComposerStatus();
        if (sendButton) {
            sendButton.disabled = true;
            sendButton.classList.add('is-sending');
        }

        try {
            const response = await window.axios.post(sendUrl, payload, {
                headers: { Accept: 'application/json' },
            });
            const message = response.data.message;

            createMessage(message);
            syncConversationContact(message);
            input.value = '';
            input.style.height = '';
            clearAttachmentPreview({ clearInput: true });
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

        if (!input.value.trim() || !typingUrl || typingRequestInFlight) {
            return;
        }

        const now = Date.now();

        if (now - lastTypingRequestAt < 2000) {
            return;
        }

        lastTypingRequestAt = now;
        typingRequestInFlight = true;
        conversationChannel?.whisper('typing', { sender_id: currentUserId });

        window.axios.post(typingUrl, {}, {
            headers: { Accept: 'application/json' },
        }).catch(() => {
            // The periodic update request still clears this state safely.
        }).finally(() => {
            typingRequestInFlight = false;
        });
    });

    attachmentInput?.addEventListener('change', () => {
        const attachment = attachmentInput.files?.[0];

        if (!attachment) {
            return;
        }

        if (attachment.size > 25 * 1024 * 1024) {
            clearAttachmentPreview({ clearInput: true });
            setComposerStatus('Attachments may not be larger than 25 MB.', true);
            return;
        }

        showAttachmentPreview(attachment);
        setComposerStatus(`${attachment.name} is ready to send.`);
    });

    window.addEventListener('chat:inbox-message', (event) => {
        const { message, incrementUnread = false } = event.detail ?? {};

        if (String(message?.conversation_id ?? '') === String(conversationId ?? '')) {
            handleConversationMessage(message);
            return;
        }

        syncConversationContact(message, incrementUnread);
    });

    window.addEventListener('chat:job-application', (event) => {
        createApplicationConversation(event.detail?.application);
    });

    const handleProfileUpdated = (event) => {
        const userId = String(event.detail?.user_id ?? '');
        const photoUrl = event.detail?.profile?.photo_url;

        if (!userId || !photoUrl) {
            return;
        }

        chat.querySelectorAll(`[data-chat-avatar-user-id="${userId}"]`).forEach((avatar) => {
            renderAvatar(avatar, { id: userId, photo_url: photoUrl });
        });
    };

    window.addEventListener('profile:updated', handleProfileUpdated);

    document.addEventListener('visibilitychange', () => {
        if (chat.isConnected && isPageVisible()) {
            markConversationRead();
            scrollToLatestMessage();
            refreshPresence();
            syncConversationUpdates();
        }
    });

    activeChatCleanup = () => {
        window.clearInterval(chatPresenceTimer);
        window.clearInterval(chatUpdatesTimer);
        window.clearTimeout(typingClearTimer);
        window.clearTimeout(longPressTimer);
        clearAttachmentPreview();
        closeReactionPicker();
        if (conversationId && window.Echo) {
            window.Echo.leave(`chat.conversation.${conversationId}`);
        }
        window.removeEventListener('profile:updated', handleProfileUpdated);
    };

    markConversationRead();
};
