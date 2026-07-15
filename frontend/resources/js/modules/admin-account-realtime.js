import Swal from 'sweetalert2';

const accountLabel = (account = {}) => account.account_type === 'employer'
    ? 'employer'
    : 'applicant';

const reviewLabel = (status = '') => ({
    approved: 'Approved',
    declined: 'Rejected',
    rejected: 'Rejected',
    pending: 'Pending',
    active: 'Active',
}[status] || 'Pending');

const initials = (name = '') => name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0))
    .join('')
    .toUpperCase() || 'AU';

const textElement = (tag, value, className = '') => {
    const element = document.createElement(tag);

    if (className) {
        element.className = className;
    }

    element.textContent = value;

    return element;
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const responseMessage = async (response) => {
    const payload = await response.json().catch(() => ({}));

    if (response.ok) {
        return payload;
    }

    const validationMessage = payload.errors
        ? Object.values(payload.errors).flat().find(Boolean)
        : null;

    throw new Error(validationMessage || payload.message || 'Unable to update this account.');
};

export const initAdminAccountRealtime = ({ showSweetToast } = {}) => {
    const body = document.body;

    if (!body.classList.contains('admin-page') || body.dataset.adminAccountRealtimeInitialized === 'true') {
        return;
    }

    body.dataset.adminAccountRealtimeInitialized = 'true';
    const handledRegistrations = new Set();

    const pageAcceptsAccount = (account) => {
        const currentType = document.querySelector('[data-admin-live-account-type]')?.dataset.adminLiveAccountType;

        return currentType === 'all' || currentType === account.account_type;
    };

    const updateSummary = ({ pending_verifications: pendingVerifications, counts } = {}) => {
        if (Number.isFinite(Number(pendingVerifications))) {
            document.querySelectorAll('[data-admin-pending-count]').forEach((element) => {
                element.textContent = String(Math.max(0, Number(pendingVerifications)));
            });
        }

        if (!counts) {
            return;
        }

        const metricValues = {
            applicant: counts.applicants,
            business: counts.employers,
        };

        Object.entries(metricValues).forEach(([key, value]) => {
            if (!Number.isFinite(Number(value))) {
                return;
            }

            document.querySelectorAll(`[data-admin-dashboard-metric="${key}"]`).forEach((element) => {
                element.textContent = String(Math.max(0, Number(value)));
            });
        });
    };

    const announceRegistration = (account) => {
        if (!pageAcceptsAccount(account)) {
            return;
        }

        document.querySelectorAll('[data-admin-registration-feed]').forEach((feed) => {
            feed.hidden = false;
            feed.textContent = `New ${accountLabel(account)} registration: ${account.name}.`;
        });
    };

    const addNotification = (account) => {
        const notificationList = document.querySelector('[data-admin-notification-list]');

        if (notificationList) {
            notificationList.querySelector('.admin-notification-empty')?.remove();

            const item = document.createElement('article');
            item.append(
                textElement('strong', account.name),
                textElement('span', `New ${accountLabel(account)} account`),
                textElement('small', account.created_at_display || 'Just now'),
            );
            notificationList.prepend(item);

            while (notificationList.children.length > 8) {
                notificationList.lastElementChild?.remove();
            }
        }

        const badge = document.querySelector('[data-admin-notification-badge]');

        if (badge) {
            const unread = Math.max(0, Number(badge.textContent) || 0) + 1;
            badge.textContent = String(unread);
            badge.hidden = false;
        }
    };

    const accountActionControls = (account) => {
        const controls = document.createElement('div');
        controls.className = 'admin-applicant-actions';
        controls.dataset.adminAccountActions = '';

        const approve = document.createElement('button');
        approve.type = 'button';
        approve.className = 'admin-account-action admin-account-action--approve';
        approve.dataset.adminAccountApproveUrl = account.approve_url || '';
        approve.dataset.accountName = account.name || 'this applicant';
        approve.textContent = 'Approve';

        const reject = document.createElement('button');
        reject.type = 'button';
        reject.className = 'admin-account-action admin-account-action--reject';
        reject.dataset.adminAccountReject = '';
        reject.dataset.rejectUrl = account.reject_url || '';
        reject.dataset.accountName = account.name || 'this applicant';
        reject.textContent = 'Reject';

        controls.append(approve, reject);

        return controls;
    };

    const prependApplicant = (account) => {
        const tableBody = document.querySelector('[data-admin-applicant-table-body]');

        if (!tableBody || account.account_type !== 'pwd_applicant' || !pageAcceptsAccount(account)) {
            return;
        }

        if (tableBody.querySelector(`[data-admin-account-id="${account.id}"]`)) {
            return;
        }

        tableBody.querySelector('[data-admin-applicant-empty]')?.remove();

        const row = document.createElement('tr');
        row.dataset.adminApplicantRow = '';
        row.dataset.adminAccountId = String(account.id);
        row.tabIndex = 0;

        const identity = document.createElement('div');
        identity.className = 'admin-applicant-identity';
        identity.append(textElement('span', initials(account.name)));
        const identityCopy = document.createElement('div');
        identityCopy.append(textElement('strong', account.name), textElement('small', account.email));
        identity.append(identityCopy);

        const identityCell = document.createElement('td');
        identityCell.append(identity);
        row.append(identityCell);

        [account.contact, account.disability, account.age].forEach((value) => row.append(textElement('td', value || 'Not set')));

        const statusCell = document.createElement('td');
        const status = textElement('span', reviewLabel(account.review_status), `admin-applicant-status admin-applicant-status--${account.review_status || 'pending'}`);
        status.dataset.adminApplicantStatus = '';
        statusCell.append(status);
        row.append(statusCell, textElement('td', account.date_display || 'Today'));

        const actionsCell = document.createElement('td');
        actionsCell.append(accountActionControls(account));
        row.append(actionsCell);
        tableBody.prepend(row);
    };

    const prependUser = (account) => {
        const tableBody = document.querySelector('[data-admin-user-table-body]');

        if (!tableBody || !pageAcceptsAccount(account)) {
            return;
        }

        if (tableBody.querySelector(`[data-admin-account-id="${account.id}"]`)) {
            return;
        }

        tableBody.querySelector('[data-admin-user-empty]')?.remove();

        const role = account.account_type === 'employer' ? 'Business' : 'Applicant';
        const roleKey = role.toLowerCase();
        const prefix = account.account_type === 'employer' ? 'BUS' : 'PWD';
        const status = account.review_status || 'pending';
        const row = document.createElement('tr');
        row.dataset.adminUserRow = '';
        row.dataset.adminAccountId = String(account.id);
        row.dataset.role = roleKey;
        row.dataset.status = status;
        row.dataset.search = `${prefix}-${String(account.id).padStart(6, '0')} ${account.name} ${account.email} ${role} ${status}`.toLocaleLowerCase();

        row.append(textElement('td', `${prefix}-${String(account.id).padStart(6, '0')}`, 'admin-user-id'));
        const userCell = document.createElement('td');
        const userIdentity = document.createElement('div');
        userIdentity.className = 'admin-user-identity';
        userIdentity.append(textElement('span', initials(account.name), `admin-user-avatar admin-user-avatar--${roleKey}`));
        const copy = document.createElement('span');
        copy.append(textElement('strong', account.name), textElement('small', account.email));
        userIdentity.append(copy);
        userCell.append(userIdentity);
        row.append(userCell, textElement('td', role), textElement('td', 'Self Register'), textElement('td', account.date_display || 'Today'));
        tableBody.prepend(row);

        const userCount = document.querySelector('[data-admin-user-count]');
        if (userCount) {
            const total = tableBody.querySelectorAll('[data-admin-user-row]').length;
            userCount.textContent = `${total} of ${total} users`;
        }
    };

    const markReviewed = (account) => {
        if (!account?.id) {
            return;
        }

        document.querySelectorAll(`[data-admin-account-id="${account.id}"]`).forEach((row) => {
            const statusElement = row.querySelector('[data-admin-applicant-status]');

            if (statusElement) {
                statusElement.className = `admin-applicant-status admin-applicant-status--${account.review_status}`;
                statusElement.textContent = reviewLabel(account.review_status);
            }

            const actions = row.querySelector('[data-admin-account-actions]');
            if (actions && account.review_status !== 'pending') {
                actions.replaceChildren(textElement('span', 'Reviewed', 'admin-account-action-complete'));
            }

            if (row.dataset.adminUserRow !== undefined) {
                row.dataset.status = account.review_status || row.dataset.status || 'pending';
            }
        });
    };

    const postDecision = async (url, formData) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: new URLSearchParams(formData),
        });

        return responseMessage(response);
    };

    const setActionsBusy = (container, isBusy) => {
        container?.querySelectorAll('button').forEach((button) => {
            if (isBusy) {
                button.dataset.originalLabel = button.textContent || '';
                button.textContent = button.classList.contains('admin-account-action--approve')
                    ? 'Approving…'
                    : button.classList.contains('admin-account-action--reject')
                        ? 'Rejecting…'
                        : button.textContent;
            } else if (button.dataset.originalLabel) {
                button.textContent = button.dataset.originalLabel;
                delete button.dataset.originalLabel;
            }

            button.disabled = isBusy;
        });
        container?.setAttribute('aria-busy', String(isBusy));
    };

    const completeDecision = (payload, title) => {
        updateSummary(payload);
        markReviewed(payload.account);
        showSweetToast?.({
            icon: payload.warning ? 'warning' : 'success',
            title,
            text: payload.warning || payload.message,
        });
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-admin-account-approve-form]')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const applicantName = form.dataset.accountName || 'this applicant';
        const confirmation = await Swal.fire({
            icon: 'question',
            title: `Approve ${applicantName}?`,
            text: 'The applicant will be notified by email and can log in after approval.',
            showCancelButton: true,
            confirmButtonText: 'Approve account',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#15803d',
            reverseButtons: true,
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        const actions = form.closest('[data-admin-account-actions]');
        setActionsBusy(actions, true);

        try {
            const payload = await postDecision(form.action, new FormData(form));
            completeDecision(payload, 'Account approved');
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Approval was not completed',
                text: error instanceof Error ? error.message : 'Unable to approve this account.',
                confirmButtonColor: '#15803d',
            });
        } finally {
            setActionsBusy(actions, false);
        }
    }, true);

    document.addEventListener('click', async (event) => {
        const target = event.target instanceof Element
            ? event.target.closest('[data-admin-account-reject], [data-admin-account-approve-url]')
            : null;

        if (!(target instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const applicantName = target.dataset.accountName || 'this applicant';
        const isApproval = Boolean(target.dataset.adminAccountApproveUrl);
        const url = isApproval ? target.dataset.adminAccountApproveUrl : target.dataset.rejectUrl;

        if (!url) {
            return;
        }

        let reason = '';

        if (isApproval) {
            const confirmation = await Swal.fire({
                icon: 'question',
                title: `Approve ${applicantName}?`,
                text: 'The applicant will be notified by email and can log in after approval.',
                showCancelButton: true,
                confirmButtonText: 'Approve account',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#15803d',
                reverseButtons: true,
            });

            if (!confirmation.isConfirmed) {
                return;
            }
        } else {
            const rejection = await Swal.fire({
                icon: 'warning',
                title: `Reject ${applicantName}?`,
                text: 'A clear reason is required and will be included in the rejection email.',
                input: 'textarea',
                inputLabel: 'Reason for rejection',
                inputPlaceholder: 'Explain what the applicant needs to correct…',
                inputAttributes: { maxlength: '500', 'aria-label': 'Reason for rejection' },
                showCancelButton: true,
                confirmButtonText: 'Reject account',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                reverseButtons: true,
                inputValidator: (value) => value.trim() ? undefined : 'A reason for rejection is required.',
            });

            if (!rejection.isConfirmed) {
                return;
            }

            reason = rejection.value.trim();
        }

        const actions = target.closest('[data-admin-account-actions]');
        setActionsBusy(actions, true);

        try {
            const payload = await postDecision(url, isApproval ? {} : { applicant_review_notes: reason });
            completeDecision(payload, isApproval ? 'Account approved' : 'Account rejected');
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Account update was not completed',
                text: error instanceof Error ? error.message : 'Unable to update this account.',
                confirmButtonColor: '#15803d',
            });
        } finally {
            setActionsBusy(actions, false);
        }
    }, true);

    const handleRegistration = (payload = {}) => {
        const account = payload.account;

        if (!account?.id || handledRegistrations.has(String(account.id))) {
            return;
        }

        handledRegistrations.add(String(account.id));
        updateSummary(payload);
        addNotification(account);
        announceRegistration(account);
        prependApplicant(account);
        prependUser(account);
        showSweetToast?.({
            icon: 'info',
            title: 'New account registration',
            text: `${account.name} registered as an ${accountLabel(account)}.`,
        });
    };

    const handleReviewUpdate = (payload = {}) => {
        updateSummary(payload);
        markReviewed(payload.account);
    };

    const updateAccountPhoto = ({ user_id: userId, profile } = {}) => {
        if (!userId || !profile?.photo_url) {
            return;
        }

        document.querySelectorAll(`[data-admin-account-id="${userId}"] [data-admin-account-avatar]`).forEach((avatar) => {
            const image = document.createElement('img');
            image.src = profile.photo_url;
            image.alt = '';
            avatar.classList.add('has-image');
            avatar.replaceChildren(image);
        });
    };

    const notificationUrl = document.querySelector('[data-admin-notifications]')?.dataset.notificationUrl;
    const syncSummary = async () => {
        if (!notificationUrl || document.visibilityState === 'hidden') {
            return;
        }

        try {
            const response = await fetch(notificationUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.ok) {
                updateSummary(await response.json());
            }
        } catch {
            // Echo remains the immediate path; this only repairs counts after a
            // temporary WebSocket interruption.
        }
    };

    syncSummary();
    window.setInterval(syncSummary, 15000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'hidden') {
            syncSummary();
        }
    });

    if (!window.Echo) {
        return;
    }

    window.Echo.private('admin.accounts')
        .listen('.account.registered', handleRegistration)
        .listen('.account.review-updated', handleReviewUpdate)
        .listen('.profile.updated', updateAccountPhoto);
};
