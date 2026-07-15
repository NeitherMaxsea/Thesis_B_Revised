export const initEmployerDashboard = () => {
    const dashboard = document.querySelector('[data-employer-dashboard]');

    if (!dashboard) return;

    const modal = dashboard.querySelector('[data-employer-job-modal]');
    const openButtons = dashboard.querySelectorAll('[data-employer-job-open]');
    const closeButton = dashboard.querySelector('[data-employer-job-close]');
    const form = dashboard.querySelector('[data-employer-job-form]');
    const submitButton = dashboard.querySelector('[data-employer-job-submit]');
    const status = dashboard.querySelector('[data-employer-job-status]');
    const jobList = dashboard.querySelector('[data-employer-job-list]');
    const jobCount = dashboard.querySelector('[data-employer-job-count]');
    const documentRenewalForms = dashboard.querySelectorAll('.employer-document-renewal');

    let opener = null;

    const openModal = (event) => {
        opener = event?.currentTarget ?? document.activeElement;
        modal?.showModal();
        modal?.querySelector('input[name="title"]')?.focus();
    };
    const closeModal = () => modal?.close();
    const setStatus = (message = '', isError = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('is-error', isError);
    };

    openButtons.forEach((button) => button.addEventListener('click', openModal));
    closeButton?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    modal?.addEventListener('close', () => {
        setStatus();
        opener?.focus?.();
    });

    const appendJob = (job) => {
        jobList?.querySelector('[data-employer-job-empty]')?.remove();

        const card = document.createElement('article');
        card.className = 'employer-job-card';
        const info = document.createElement('div');
        const badge = document.createElement('span');
        badge.className = 'employer-job-card__status';
        badge.textContent = job.status[0].toUpperCase() + job.status.slice(1);
        const title = document.createElement('h3');
        title.textContent = job.title;
        const details = document.createElement('p');
        details.textContent = `${job.location} · ${job.employment_type}`;
        info.append(badge, title, details);
        const metadata = document.createElement('dl');
        metadata.innerHTML = `<div><dt>Vacancies</dt><dd>${job.vacancies}</dd></div><div><dt>Posted</dt><dd>Just now</dd></div>`;
        card.append(info, metadata);
        jobList?.prepend(card);

        if (jobCount) jobCount.textContent = String(Number(jobCount.textContent || 0) + 1);
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setStatus();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Publishing…';

        try {
            const response = await window.axios.post(form.action, new FormData(form), {
                headers: { Accept: 'application/json' },
            });
            appendJob(response.data.job);
            form.reset();
            closeModal();
        } catch (error) {
            const message = error.response?.data?.errors
                ? Object.values(error.response.data.errors).flat()[0]
                : error.response?.data?.message ?? 'Unable to publish this job post.';
            setStatus(message, true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Publish job post';
        }
    });

    documentRenewalForms.forEach((renewalForm) => {
        renewalForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!renewalForm.checkValidity()) {
                renewalForm.reportValidity();
                return;
            }

            const button = renewalForm.querySelector('button[type="submit"]');
            const originalText = button?.textContent || 'Renew';
            if (button) {
                button.disabled = true;
                button.textContent = 'Renewing…';
            }

            try {
                const response = await window.axios.post(renewalForm.action, new FormData(renewalForm), {
                    headers: { Accept: 'application/json' },
                });

                window.dispatchEvent(new CustomEvent('workspace:navigate', {
                    detail: { url: window.location.href, replace: true },
                }));
            } catch (error) {
                const message = error.response?.data?.message
                    ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
                    ?? 'Unable to renew the document. Please try again.';
                setStatus(message, true);
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText;
                }
            }
        });
    });
};
