// Keeps a signed-in user's presence current while they are actively using the app.
export const initUserActivity = () => {
    const body = document.body;
    const activityUrl = body.dataset.activityUrl;

    if (!activityUrl || body.dataset.userActivityInitialized === 'true') {
        return;
    }

    body.dataset.userActivityInitialized = 'true';

    const heartbeat = async () => {
        if (document.visibilityState === 'hidden') {
            return;
        }

        try {
            await window.axios.post(activityUrl);
        } catch {
            // Presence is supplementary; a later heartbeat will retry.
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            heartbeat();
        }
    });

    window.addEventListener('focus', heartbeat);
    heartbeat();
    window.setInterval(heartbeat, 60000);
};
