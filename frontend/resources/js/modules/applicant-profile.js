const getInitials = (name) => name
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join('');

export const initApplicantProfile = () => {
    document.querySelectorAll('[data-phone-input]').forEach((phoneField) => {
        phoneField.addEventListener('input', () => {
            const digits = phoneField.value.replace(/\D/g, '').slice(0, 10);
            if (phoneField.value !== digits) phoneField.value = digits;
        });
    });

    const profile = document.querySelector('[data-applicant-profile]');

    if (!profile) {
        return;
    }

    const userId = profile.dataset.userId;
    const form = profile.querySelector('[data-profile-form]');
    const saveButton = profile.querySelector('[data-profile-save]');
    const status = profile.querySelector('[data-profile-form-status]');

    const setStatus = (message = '', isError = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('is-error', isError);
    };

    const applyProfile = (data) => {
        const fullName = data.full_name || data.name;
        document.querySelectorAll('[data-dashboard-user-name]').forEach((element) => { element.textContent = data.name; });
        document.querySelectorAll('[data-dashboard-user-disability]').forEach((element) => { element.textContent = data.disability; });
        profile.querySelector('[data-profile-full-name]').textContent = fullName;
        profile.querySelector('[data-profile-disability]').textContent = data.disability;
        profile.querySelector('[data-profile-email]').textContent = data.email;
        profile.querySelector('[data-profile-location]').textContent = `${data.street_address}, ${data.city || 'Dasmarinas'}`;
        profile.querySelector('[data-profile-avatar]').textContent = getInitials(fullName) || 'PA';
    };

    if (userId && window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.profile.updated', ({ profile: updatedProfile }) => {
                applyProfile(updatedProfile);
                setStatus('Your profile was updated in another session.');
            });
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setStatus();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Saving…';

        try {
            const response = await window.axios.post(form.action, new FormData(form), {
                headers: { Accept: 'application/json' },
            });
            applyProfile(response.data.profile);
            setStatus('Profile saved and synced securely.');
        } catch (error) {
            const message = error.response?.data?.errors
                ? Object.values(error.response.data.errors).flat()[0]
                : error.response?.data?.message ?? 'Unable to save your profile. Please try again.';
            setStatus(message, true);
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save changes';
        }
    });
};
