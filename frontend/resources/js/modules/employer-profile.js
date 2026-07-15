export const initEmployerProfile = () => {
    const profile = document.querySelector('[data-employer-profile]');

    if (!profile || profile.dataset.employerProfileInitialized === 'true') return;

    profile.dataset.employerProfileInitialized = 'true';

    const form = profile.querySelector('[data-employer-profile-form]');
    const saveButton = profile.querySelector('[data-employer-profile-save]');
    const status = profile.querySelector('[data-employer-profile-status]');
    const phoneField = profile.querySelector('[data-phone-input]');
    const photoInput = profile.querySelector('[data-employer-profile-photo-input]');
    const profileAvatar = profile.querySelector('[data-employer-profile-avatar]');
    let photoPreviewUrl = '';

    const getInitials = (name) => name
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');

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
            image.alt = avatar.dataset.employerNavbarAvatar !== undefined ? '' : `Profile photo of ${name}`;
            avatar.replaceChildren(image);
            return;
        }

        avatar.textContent = getInitials(name) || 'EH';
    };

    const updateAvatars = (photoUrl, name) => {
        setAvatar(profileAvatar, photoUrl, name);
        document.querySelectorAll('[data-employer-navbar-avatar]').forEach((avatar) => {
            setAvatar(avatar, photoUrl, name);
        });
    };

    const releasePhotoPreview = () => {
        if (!photoPreviewUrl) return;
        URL.revokeObjectURL(photoPreviewUrl);
        photoPreviewUrl = '';
    };

    phoneField?.addEventListener('input', () => {
        const digits = phoneField.value.replace(/\D/g, '').slice(0, 10);
        if (phoneField.value !== digits) phoneField.value = digits;
    });

    photoInput?.addEventListener('change', () => {
        const [photo] = photoInput.files || [];
        if (!photo) return;

        releasePhotoPreview();
        photoPreviewUrl = URL.createObjectURL(photo);
        const name = form?.querySelector('[name="company_name"]')?.value || 'Employer';
        updateAvatars(photoPreviewUrl, name);
        setStatus('New photo selected. Save changes to update your profile.');
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setStatus();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const originalText = saveButton?.textContent || 'Save changes';
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = 'Saving…';
        }

        try {
            const response = await window.axios.post(form.action, new FormData(form), {
                headers: { Accept: 'application/json' },
            });
            const updated = response.data.profile;

            releasePhotoPreview();
            document.querySelectorAll('[data-employer-profile-name], [data-employer-navbar-name]').forEach((element) => {
                element.textContent = updated.company_name;
            });
            updateAvatars(updated.photo_url, updated.company_name);
            setStatus(response.data.message || 'Business profile updated.');
        } catch (error) {
            const message = error.response?.data?.message
                ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
                ?? 'Unable to save your business profile. Please try again.';
            setStatus(message, true);
        } finally {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = originalText;
            }
        }
    });
};
