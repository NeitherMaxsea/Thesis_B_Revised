export const initJobMatch = () => {
    const jobMatch = document.querySelector('[data-job-match]');

    if (!jobMatch) return;

    const search = jobMatch.querySelector('[data-job-match-search]');
    const cards = [...jobMatch.querySelectorAll('[data-job-match-card]')];
    const update = jobMatch.querySelector('[data-job-match-update]');
    const refresh = jobMatch.querySelector('[data-job-match-refresh]');
    const liveStatus = jobMatch.querySelector('[data-job-match-live-status]');

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        cards.forEach((card) => { card.hidden = query !== '' && !card.dataset.jobMatchSearchText.includes(query); });
    });

    refresh?.addEventListener('click', () => window.location.reload());

    if (window.Echo) {
        window.Echo.channel('jobs').listen('.job.posted', () => {
            update.hidden = false;
        });
    } else if (liveStatus) {
        liveStatus.textContent = 'Live job updates unavailable';
    }
};
