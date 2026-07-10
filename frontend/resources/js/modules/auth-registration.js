import i18next from 'i18next';

// APPLICANT + EMPLOYER registration UI. Backend validation remains in AuthController.
export const initAuthRegistration = ({ applyTranslations, getCurrentInterfaceLanguage, showSweetToast }) => {
    const authTabs = document.querySelectorAll('[data-auth-tab]');
    const authPanels = document.querySelectorAll('[data-auth-panel]');
    const authSwitches = document.querySelectorAll('[data-auth-switch]');
    const authRoleCards = document.querySelectorAll('[data-auth-role]');
    const authRoleInput = document.querySelector('[data-auth-role-input]');
    const registerForm = document.querySelector('[data-register-form]');
    const authNavLinks = document.querySelectorAll('[data-auth-nav-link]');
    
    let setAuthMode = () => {};
    
    if (authTabs.length && authPanels.length) {
        setAuthMode = (mode) => {
            const normalizedMode = mode === 'register' ? 'register' : 'login';
    
            authTabs.forEach((tab) => {
                const isActive = tab.dataset.authTab === normalizedMode;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', String(isActive));
            });
    
            authPanels.forEach((panel) => {
                panel.classList.toggle('is-hidden', panel.dataset.authPanel !== normalizedMode);
            });
    
            const targetPath = normalizedMode === 'register' ? '/register' : '/login';
    
            if (['/login', '/register'].includes(window.location.pathname) && window.location.pathname !== targetPath) {
                window.history.replaceState({}, '', targetPath);
            }
    
            applyTranslations(getCurrentInterfaceLanguage());
        };
    
        authTabs.forEach((tab) => {
            tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab));
        });
    
        authSwitches.forEach((switchButton) => {
            switchButton.addEventListener('click', () => setAuthMode(switchButton.dataset.authSwitch));
        });
    }
    
    if (authNavLinks.length && authPanels.length) {
        authNavLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const mode = link.dataset.authNavLink;
    
                if (!mode || !['/login', '/register'].includes(window.location.pathname)) {
                    return;
                }
    
                event.preventDefault();
                setAuthMode(mode);
    
                const targetPath = new URL(link.href, window.location.origin).pathname;
    
                if (window.location.pathname !== targetPath) {
                    window.history.replaceState({}, '', targetPath);
                }
            });
        });
    }
    
    if (registerForm) {
        const registerSteps = registerForm.querySelectorAll('[data-register-step]');
        const registerStepButtons = registerForm.querySelectorAll('[data-register-step-button]');
        const registerPreviousButton = registerForm.querySelector('[data-register-prev]');
        const registerNextButton = registerForm.querySelector('[data-register-next]');
        const registerSubmitButton = registerForm.querySelector('[data-register-submit]');
        const registerStepper = registerForm.querySelector('[data-register-stepper]');
        const registerCopy = registerForm.querySelector('[data-register-copy]');
        const roleSwitchNotice = registerForm.querySelector('[data-role-switch-notice]');
        const birthdateInput = registerForm.querySelector('[data-birthdate]');
        const ageInput = registerForm.querySelector('[data-birth-age]');
        const nameInputs = registerForm.querySelectorAll('[data-name-field]');
        const requiredFields = registerForm.querySelectorAll('[data-step-required]');
        const applicantOnlyControls = registerForm.querySelectorAll('[data-applicant-only] input, [data-applicant-only] select, [data-applicant-only] textarea');
        const employerOnlyControls = registerForm.querySelectorAll('[data-employer-only] input, [data-employer-only] select, [data-employer-only] textarea');
        const applicantOnlyElements = registerForm.querySelectorAll('[data-applicant-only]');
        const employerOnlyElements = registerForm.querySelectorAll('[data-employer-only]');
        const passwordInput = registerForm.querySelector('#register-password');
        const passwordConfirmationInput = registerForm.querySelector('#register-password-confirmation');
        const pwdIdInput = registerForm.querySelector('#register-pwd-id');
        const pwdIdFileName = registerForm.querySelector('[data-file-name]');
        const phoneInputs = registerForm.querySelectorAll('[data-phone-input]');
        const employerFileInputs = registerForm.querySelectorAll('[data-employer-file-input]');
        const finalConfirmationCopy = registerForm.querySelector('[data-final-confirmation-copy]');
        const defaultPwdIdFileName = pwdIdFileName?.textContent.trim() || 'No file selected';
    
        let currentRegisterStep = 1;
        let registerAdjustTimer;
        let isSubmittingRegistration = false;
        registerForm.noValidate = true;
    
        const getAccountType = () => (authRoleInput?.value === 'employer' ? 'employer' : 'pwd_applicant');
        const isEmployerAccount = () => getAccountType() === 'employer';
        const getStepsToValidate = () => [1, 2, 3];
    
        const setPasswordConfirmationValidity = () => {
            if (!passwordInput || !passwordConfirmationInput) {
                return;
            }
    
            const isMismatch =
                passwordConfirmationInput.value.length > 0 &&
                passwordInput.value !== passwordConfirmationInput.value;
    
            passwordConfirmationInput.setCustomValidity(isMismatch ? i18next.t('Passwords must match.', { defaultValue: 'Passwords must match.' }) : '');
        };
    
        const syncPwdIdFileName = () => {
            if (!pwdIdInput || !pwdIdFileName) {
                return;
            }
    
            pwdIdFileName.textContent = pwdIdInput.files?.[0]?.name || defaultPwdIdFileName;
        };
    
        const hasApplicantDraftData = () => Array.from(applicantOnlyControls).some((field) => {
            if (field.id === 'register-city') {
                return false;
            }
    
            if (field.type === 'file') {
                return field.files?.length > 0;
            }
    
            if (field.type === 'checkbox' || field.type === 'radio') {
                return field.checked;
            }
    
            return field.value.trim() !== '';
        });
    
        const sanitizeNameInput = (input) => {
            const cleanedValue = input.value
                .replace(/[0-9]/g, '')
                .replace(/\s{2,}/g, ' ');
    
            if (input.value !== cleanedValue) {
                input.value = cleanedValue;
            }
        };
    
        const sanitizeAgeInput = () => {
            if (!ageInput) {
                return;
            }
    
            const cleanedValue = ageInput.value.replace(/\D/g, '').slice(0, 3);
    
            if (ageInput.value !== cleanedValue) {
                ageInput.value = cleanedValue;
            }
        };
    
        const syncRequiredFields = () => {
            requiredFields.forEach((field) => field.toggleAttribute('required', !field.disabled));
    
            setPasswordConfirmationValidity();
        };
    
        const getFirstInvalidField = (step) => {
            syncRequiredFields();
    
            const fields = Array.from(registerForm.querySelectorAll(`[data-register-step="${step}"] [data-step-required]`));
    
            return fields.find((field) => !field.disabled && !field.checkValidity()) ?? null;
        };
    
        const reportInvalidField = (field) => {
            window.requestAnimationFrame(() => {
                field.focus({ preventScroll: false });
                field.reportValidity();
            });
        };
    
        const validateStep = (step) => {
            const invalidField = getFirstInvalidField(step);
    
            if (!invalidField) {
                return true;
            }
    
            reportInvalidField(invalidField);
            return false;
        };
    
        const validateRegistration = () => {
            for (const step of getStepsToValidate()) {
                const invalidField = getFirstInvalidField(step);
    
                if (invalidField) {
                    setRegisterStep(step);
                    reportInvalidField(invalidField);
                    return false;
                }
            }
    
            return true;
        };
    
        const setRegisterStep = (step) => {
            currentRegisterStep = Math.min(3, Math.max(1, Number(step)));
    
            registerSteps.forEach((panel) => {
                panel.classList.toggle('is-hidden', Number(panel.dataset.registerStep) !== currentRegisterStep);
            });
    
            registerStepButtons.forEach((button) => {
                const stepNumber = Number(button.dataset.registerStepButton);
                const isActive = stepNumber === currentRegisterStep;
    
                button.classList.toggle('is-active', isActive);
                button.classList.toggle('is-complete', stepNumber < currentRegisterStep);
                button.setAttribute('aria-current', isActive ? 'step' : 'false');
                button.setAttribute('aria-disabled', 'true');
                button.disabled = true;
            });
    
            registerPreviousButton?.classList.toggle('is-hidden', currentRegisterStep === 1);
            registerNextButton?.classList.toggle('is-hidden', currentRegisterStep === 3);
            registerSubmitButton?.classList.toggle('is-hidden', currentRegisterStep !== 3);
            syncRequiredFields();
        };
    
        // ROLE SWITCH: controls which form steps are visible. Keep this aligned with AuthController::register().
        const syncRegisterMode = () => {
            const isEmployer = isEmployerAccount();
    
            registerForm.classList.add('is-adjusting');
            window.clearTimeout(registerAdjustTimer);
            registerAdjustTimer = window.setTimeout(() => registerForm.classList.remove('is-adjusting'), 220);
    
            registerStepper?.classList.remove('is-hidden');
            if (registerCopy) {
                registerCopy.textContent = isEmployer
                    ? 'Complete the three-step business verification before posting jobs.'
                    : 'Complete each applicant step before account setup.';
            }
    
            applicantOnlyControls.forEach((field) => {
                field.disabled = isEmployer || field.id === 'register-city';
            });
    
            employerOnlyControls.forEach((field) => {
                field.disabled = !isEmployer;
            });

            applicantOnlyElements.forEach((element) => element.classList.toggle('is-hidden', isEmployer));
            employerOnlyElements.forEach((element) => element.classList.toggle('is-hidden', !isEmployer));

            if (finalConfirmationCopy) {
                finalConfirmationCopy.textContent = isEmployer
                    ? 'I confirm that the uploaded business documents are current, authentic, and eligible for verification.'
                    : 'I confirm that my information is correct and my PWD ID is valid for verification.';
            }

            setRegisterStep(1);
            applyTranslations(getCurrentInterfaceLanguage());
        };
    
        const setAuthRole = (role) => {
            const normalizedRole = role === 'employer' ? 'employer' : 'pwd_applicant';
    
            if (
                normalizedRole === 'employer' &&
                getAccountType() === 'pwd_applicant' &&
                hasApplicantDraftData()
            ) {
                roleSwitchNotice?.classList.remove('hidden');
                roleSwitchNotice?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                return;
            }
    
            roleSwitchNotice?.classList.add('hidden');
    
            if (authRoleInput) {
                authRoleInput.value = normalizedRole;
            }
    
            authRoleCards.forEach((roleCard) => {
                const isActive = roleCard.dataset.authRole === normalizedRole;
    
                roleCard.classList.toggle('is-active', isActive);
                roleCard.setAttribute('aria-pressed', String(isActive));
            });
    
            syncRegisterMode();
        };
    
        const syncAgeFromBirthdate = () => {
            if (!birthdateInput?.value || !ageInput) {
                return;
            }
    
            const birthdate = new Date(`${birthdateInput.value}T00:00:00`);
    
            if (Number.isNaN(birthdate.getTime())) {
                return;
            }
    
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const hasBirthdayPassed =
                today.getMonth() > birthdate.getMonth() ||
                (today.getMonth() === birthdate.getMonth() && today.getDate() >= birthdate.getDate());
    
            if (!hasBirthdayPassed) {
                age -= 1;
            }
    
            if (age > 0) {
                ageInput.value = String(age);
            }
        };
    
        registerStepButtons.forEach((button) => {
            button.disabled = true;
            button.setAttribute('aria-disabled', 'true');
        });
    
        registerPreviousButton?.addEventListener('click', () => setRegisterStep(currentRegisterStep - 1));
        registerNextButton?.addEventListener('click', () => {
            if (!validateStep(currentRegisterStep)) {
                return;
            }

            const originalText = registerNextButton.textContent.trim();
            registerNextButton.disabled = true;
            registerNextButton.classList.add('is-loading');
            registerNextButton.textContent = 'Preparing…';

            window.setTimeout(() => {
                setRegisterStep(currentRegisterStep + 1);
                registerNextButton.disabled = false;
                registerNextButton.classList.remove('is-loading');
                registerNextButton.textContent = originalText;
            }, 520);
        });
    
        registerForm.addEventListener('submit', (event) => {
            if (isSubmittingRegistration) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
    
            if (!validateRegistration()) {
                event.preventDefault();
                event.stopPropagation();
                showSweetToast({
                    title: 'Please complete the form',
                    text: 'Check the highlighted field before creating your account.',
                    icon: 'warning',
                });
                return;
            }
    
            isSubmittingRegistration = true;
            registerSubmitButton?.setAttribute('disabled', 'disabled');
            registerNextButton?.setAttribute('disabled', 'disabled');
            registerPreviousButton?.setAttribute('disabled', 'disabled');
            registerSubmitButton?.classList.add('is-loading');
    
            if (registerSubmitButton) {
                registerSubmitButton.dataset.originalText = registerSubmitButton.textContent.trim();
                registerSubmitButton.textContent = 'Creating account...';
            }
    
            showSweetToast({
                title: 'Creating account',
                text: 'Please wait while we save your details and prepare verification.',
                loading: true,
            });
        });
    
        authRoleCards.forEach((card) => {
            card.addEventListener('click', () => setAuthRole(card.dataset.authRole));
        });
    
        passwordInput?.addEventListener('input', setPasswordConfirmationValidity);
        passwordConfirmationInput?.addEventListener('input', setPasswordConfirmationValidity);
        nameInputs.forEach((input) => {
            input.addEventListener('input', () => sanitizeNameInput(input));
        });
        ageInput?.addEventListener('keydown', (event) => {
            if (['e', 'E', '+', '-', '.', ','].includes(event.key)) {
                event.preventDefault();
            }
        });
        ageInput?.addEventListener('input', sanitizeAgeInput);
        birthdateInput?.addEventListener('change', syncAgeFromBirthdate);
        pwdIdInput?.addEventListener('change', syncPwdIdFileName);
        employerFileInputs.forEach((input) => {
            input.addEventListener('change', () => {
                const fileName = registerForm.querySelector(`[data-employer-file-name="${input.name}"]`);
                if (fileName) fileName.textContent = input.files?.[0]?.name || 'No file selected';
            });
        });
        phoneInputs.forEach((input) => {
            input.addEventListener('input', () => {
                const digits = input.value.replace(/\D/g, '').slice(0, 10);
                if (input.value !== digits) input.value = digits;
            });
        });
    
        syncAgeFromBirthdate();
        nameInputs.forEach(sanitizeNameInput);
        sanitizeAgeInput();
        syncPwdIdFileName();
        setAuthRole(authRoleInput?.value);
    }
};
