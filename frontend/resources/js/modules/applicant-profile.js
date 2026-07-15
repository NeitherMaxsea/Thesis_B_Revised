const getInitials = (name) => name
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join('');

let activeProfileCleanup = () => {};

export const destroyApplicantProfile = () => {
    activeProfileCleanup();
    activeProfileCleanup = () => {};
};

export const initApplicantProfile = () => {
    const profile = document.querySelector('[data-applicant-profile]');

    if (!profile) {
        return;
    }

    // Workspace navigation replaces the page body in place. Clear the old
    // realtime callback first so revisiting Profile does not stack listeners.
    destroyApplicantProfile();

    profile.querySelectorAll('[data-phone-input]').forEach((phoneField) => {
        phoneField.addEventListener('input', () => {
            const digits = phoneField.value.replace(/\D/g, '').slice(0, 10);
            if (phoneField.value !== digits) phoneField.value = digits;
        });
    });

    const userId = profile.dataset.userId;
    const form = profile.querySelector('[data-profile-form]');
    const saveButton = profile.querySelector('[data-profile-save]');
    const status = profile.querySelector('[data-profile-form-status]');
    const photoInput = profile.querySelector('[data-profile-photo-input]');
    const profileAvatar = profile.querySelector('[data-profile-avatar]');
    const generalDisabilityInput = profile.querySelector('[data-profile-general-disability]');
    const disabilityCategoryInput = profile.querySelector('[data-profile-disability-category]');
    let profileChannel = null;
    let photoPreviewUrl = '';
    const disabilityCategories = (() => {
        try {
            return JSON.parse(profile.dataset.disabilityCategories || '{}');
        } catch {
            return {};
        }
    })();

    const syncDisabilityCategory = ({ preserveSelection = true } = {}) => {
        if (!(generalDisabilityInput instanceof HTMLSelectElement) || !(disabilityCategoryInput instanceof HTMLSelectElement)) {
            return;
        }

        const selectedValue = preserveSelection ? disabilityCategoryInput.value : '';
        const categories = disabilityCategories[generalDisabilityInput.value] || [];

        disabilityCategoryInput.replaceChildren(new Option(
            generalDisabilityInput.value ? 'Select disability category' : 'Select general category first',
            '',
        ));
        categories.forEach((category) => disabilityCategoryInput.add(new Option(category, category)));
        disabilityCategoryInput.disabled = !generalDisabilityInput.value;

        if (categories.includes(selectedValue)) {
            disabilityCategoryInput.value = selectedValue;
        }
    };

    const setStatus = (message = '', isError = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.toggle('is-error', isError);
    };

    const setAvatar = (avatar, photoUrl, name) => {
        if (!avatar) return;

        avatar.classList.toggle('has-image', Boolean(photoUrl));

        if (photoUrl) {
            const image = document.createElement('img');
            image.src = photoUrl;
            image.alt = avatar.dataset.dashboardAvatar !== undefined ? '' : `Profile photo of ${name}`;
            image.dataset.profileAvatarImage = '';
            avatar.replaceChildren(image);
            return;
        }

        avatar.textContent = getInitials(name) || 'PA';
    };

    const releasePhotoPreview = () => {
        if (photoPreviewUrl) {
            URL.revokeObjectURL(photoPreviewUrl);
            photoPreviewUrl = '';
        }
    };

    const applyProfile = (data) => {
        const fullName = data.full_name || data.name;
        document.querySelectorAll('[data-dashboard-user-name]').forEach((element) => { element.textContent = data.name; });
        document.querySelectorAll('[data-dashboard-user-disability]').forEach((element) => { element.textContent = data.disability; });
        profile.querySelector('[data-profile-full-name]').textContent = fullName;
        profile.querySelector('[data-profile-disability]').textContent = data.disability;
        profile.querySelector('[data-profile-email]').textContent = data.email;
        profile.querySelector('[data-profile-location]').textContent = `${data.street_address}, ${data.city || 'Dasmarinas'}`;
        setAvatar(profileAvatar, data.photo_url, fullName);
        document.querySelectorAll('[data-dashboard-avatar]').forEach((avatar) => {
            setAvatar(avatar, data.photo_url, fullName);
        });
    };

    if (userId && window.Echo) {
        profileChannel = window.Echo.private(`App.Models.User.${userId}`);
        profileChannel
            .listen('.profile.updated', ({ profile: updatedProfile }) => {
                applyProfile(updatedProfile);
                setStatus('Your profile was updated in another session.');
            });
    }

    activeProfileCleanup = () => {
        // This private user channel is also used by the inbox, so remove only
        // this event listener instead of leaving the whole channel.
        profileChannel?.stopListening('.profile.updated');
        releasePhotoPreview();
    };

    photoInput?.addEventListener('change', () => {
        const [photo] = photoInput.files || [];

        if (!photo) return;

        releasePhotoPreview();
        photoPreviewUrl = URL.createObjectURL(photo);
        const name = `${profile.querySelector('[data-profile-first-name]')?.value || ''} ${profile.querySelector('[data-profile-last-name]')?.value || ''}`.trim();
        setAvatar(profileAvatar, photoPreviewUrl, name || 'Applicant');
        document.querySelectorAll('[data-dashboard-avatar]').forEach((avatar) => {
            setAvatar(avatar, photoPreviewUrl, name || 'Applicant');
        });
        setStatus('New photo selected. Save changes to update your profile.');
    });

    generalDisabilityInput?.addEventListener('change', () => syncDisabilityCategory({ preserveSelection: false }));
    syncDisabilityCategory();

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
            releasePhotoPreview();
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
