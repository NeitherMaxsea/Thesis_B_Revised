let activeJobMatchCleanup = () => {};

export const destroyJobMatch = () => {
    activeJobMatchCleanup();
    activeJobMatchCleanup = () => {};
};

export const initJobMatch = () => {
    const jobMatch = document.querySelector('[data-job-match]');

    if (!jobMatch || jobMatch.dataset.jobMatchInitialized === 'true') return;

    // The workspace shell stays mounted while navigating. Tear down the old
    // job-event callback before attaching one for the replacement content.
    destroyJobMatch();
    jobMatch.dataset.jobMatchInitialized = 'true';

    const search = jobMatch.querySelector('[data-job-match-search]');
    const cards = [...jobMatch.querySelectorAll('[data-job-match-card]')];
    const update = jobMatch.querySelector('[data-job-match-update]');
    const refresh = jobMatch.querySelector('[data-job-match-refresh]');
    const liveStatus = jobMatch.querySelector('[data-job-match-live-status]');

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        cards.forEach((card) => { card.hidden = query !== '' && !card.dataset.jobMatchSearchText.includes(query); });
    });

    const refreshJobs = async () => {
        if (!refresh || refresh.disabled) return;

        const originalText = refresh.textContent;
        refresh.disabled = true;
        refresh.textContent = 'Refreshing…';

        try {
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error('Unable to refresh jobs.');

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextJobMatch = page.querySelector('[data-job-match]');

            if (!nextJobMatch) throw new Error('Unable to refresh jobs.');

            destroyJobMatch();
            jobMatch.replaceWith(nextJobMatch);
            initJobMatch();
        } catch {
            refresh.disabled = false;
            refresh.textContent = originalText;
            if (liveStatus) liveStatus.textContent = 'Could not refresh jobs. Please try again.';
        }
    };

    refresh?.addEventListener('click', refreshJobs);

    const applicationForm = jobMatch.querySelector('[data-job-application-form]');
    const applicationButton = jobMatch.querySelector('[data-job-application-button]');
    const applicationStatus = jobMatch.querySelector('[data-job-application-status]');

    applicationForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!applicationButton || applicationButton.disabled) return;

        const originalText = applicationButton.textContent.trim();
        applicationButton.disabled = true;
        applicationButton.textContent = 'Starting secure message…';
        if (applicationStatus) applicationStatus.textContent = '';

        try {
            const response = await window.axios.post(applicationForm.action, new FormData(applicationForm), {
                headers: { Accept: 'application/json' },
            });

            if (applicationStatus) applicationStatus.textContent = response.data.message;
            window.dispatchEvent(new CustomEvent('workspace:navigate', {
                detail: { url: response.data.conversation_url },
            }));
        } catch (error) {
            const message = error.response?.data?.message
                ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
                ?? 'Your application could not be submitted. Please try again.';
            if (applicationStatus) applicationStatus.textContent = message;
            applicationButton.disabled = false;
            applicationButton.textContent = originalText;
        }
    });

    if (window.Echo) {
        const jobsChannel = window.Echo.channel('jobs');
        jobsChannel.listen('.job.posted', () => {
            update.hidden = false;
        });
        activeJobMatchCleanup = () => {
            jobsChannel.stopListening('.job.posted');
        };
    } else if (liveStatus) {
        liveStatus.textContent = 'Live job updates unavailable';
    }
};
