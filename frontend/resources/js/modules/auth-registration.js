import i18next from 'i18next';

// APPLICANT + EMPLOYER registration UI. Backend validation remains in AuthController.
export const initAuthRegistration = ({ applyTranslations, getCurrentInterfaceLanguage, showSweetToast }) => {
    // Shared by login plus both applicant and employer registration paths.
    // Keeping the input type as password by default preserves browser security
    // and autofill behavior; visibility changes only after an explicit click.
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        if (toggle.dataset.passwordToggleInitialized === 'true') {
            return;
        }

        const passwordInput = document.getElementById(toggle.dataset.passwordTarget);

        if (!(passwordInput instanceof HTMLInputElement)) {
            return;
        }

        const syncPasswordVisibility = (isVisible) => {
            passwordInput.type = isVisible ? 'text' : 'password';
            const label = isVisible ? 'Hide password' : 'Show password';

            toggle.setAttribute('aria-pressed', String(isVisible));
            toggle.setAttribute('aria-label', label);
            toggle.title = label;
            toggle.querySelector('[data-password-icon="show"]')?.toggleAttribute('hidden', isVisible);
            toggle.querySelector('[data-password-icon="hide"]')?.toggleAttribute('hidden', !isVisible);
            const accessibleText = toggle.querySelector('[data-password-toggle-text]');
            if (accessibleText) accessibleText.textContent = label;
        };

        toggle.dataset.passwordToggleInitialized = 'true';
        syncPasswordVisibility(false);
        toggle.addEventListener('click', () => {
            syncPasswordVisibility(passwordInput.type === 'password');
            passwordInput.focus({ preventScroll: true });
        });
    });

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
        const registerActions = registerForm.querySelector('[data-register-actions]');
        const roleSelector = registerForm.querySelector('[data-register-role-selector]');
        const roleSummary = registerForm.querySelector('[data-register-role-summary]');
        const roleSummaryLabel = registerForm.querySelector('[data-register-role-summary-label]');
        const changeRoleButton = registerForm.querySelector('[data-register-change-role]');
        const roleLockedNotice = registerForm.querySelector('[data-role-locked-notice]');
        const roleSwitchNotice = registerForm.querySelector('[data-role-switch-notice]');
        const birthdateInput = registerForm.querySelector('[data-birthdate]');
        const birthdateValueInput = registerForm.querySelector('[data-birthdate-value]');
        const datePicker = registerForm.querySelector('[data-date-picker]');
        const datePickerToggle = registerForm.querySelector('[data-date-picker-toggle]');
        const datePickerPopover = registerForm.querySelector('[data-date-picker-popover]');
        const datePickerTitle = registerForm.querySelector('[data-date-picker-title]');
        const datePickerDays = registerForm.querySelector('[data-date-picker-days]');
        const datePickerPrevious = registerForm.querySelector('[data-date-picker-previous]');
        const datePickerNext = registerForm.querySelector('[data-date-picker-next]');
        const datePickerClear = registerForm.querySelector('[data-date-picker-clear]');
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
        const generalDisabilityInput = registerForm.querySelector('[data-general-disability]');
        const disabilityCategoryInput = registerForm.querySelector('[data-disability-category]');
        const finalConfirmationCopy = registerForm.querySelector('[data-final-confirmation-copy]');
        const defaultPwdIdFileName = pwdIdFileName?.textContent.trim() || 'No file selected';
    
        let currentRegisterStep = 1;
        let registerAdjustTimer;
        let isSubmittingRegistration = false;
        let hasChosenRole = registerForm.dataset.registerHasErrors === 'true';
        registerForm.noValidate = true;
    
        const getAccountType = () => (authRoleInput?.value === 'employer' ? 'employer' : 'pwd_applicant');
        const isEmployerAccount = () => getAccountType() === 'employer';
        const getStepsToValidate = () => [1, 2, 3];
        const disabilityCategories = (() => {
            try {
                return JSON.parse(registerForm.dataset.disabilityCategories || '{}');
            } catch {
                return {};
            }
        })();

        const syncRoleSelectionVisibility = () => {
            const shouldShowRegistration = hasChosenRole;

            roleSelector?.classList.toggle('is-hidden', shouldShowRegistration);
            roleSummary?.classList.toggle('is-hidden', !shouldShowRegistration);
            registerStepper?.classList.toggle('is-hidden', !shouldShowRegistration);
            registerActions?.classList.toggle('is-hidden', !shouldShowRegistration);
            registerSteps.forEach((panel) => {
                panel.classList.toggle(
                    'is-hidden',
                    !shouldShowRegistration || Number(panel.dataset.registerStep) !== currentRegisterStep,
                );
            });

            if (registerCopy && !shouldShowRegistration) {
                registerCopy.textContent = 'Choose whether you are registering as a PWD Applicant or Employer.';
            }
            if (roleSummaryLabel) roleSummaryLabel.textContent = isEmployerAccount() ? 'Employer' : 'PWD Applicant';
        };
    
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
    
        const hasDraftData = (controls) => Array.from(controls).some((field) => {
            if (field.id === 'register-city') return false;
            if (field.type === 'file') return field.files?.length > 0;
            if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
            return field.value.trim() !== '';
        });

        const hasApplicantDraftData = () => hasDraftData(applicantOnlyControls);
        const hasEmployerDraftData = () => hasDraftData(employerOnlyControls);
        const hasCurrentRoleDraftData = () => (isEmployerAccount()
            ? hasEmployerDraftData()
            : hasApplicantDraftData());
    
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

        const syncDisabilityCategory = ({ preserveSelection = true } = {}) => {
            if (!(generalDisabilityInput instanceof HTMLSelectElement) || !(disabilityCategoryInput instanceof HTMLSelectElement)) {
                return;
            }

            const selectedValue = preserveSelection
                ? (disabilityCategoryInput.value || disabilityCategoryInput.dataset.selectedDisabilityCategory || '')
                : '';
            const categories = disabilityCategories[generalDisabilityInput.value] || [];

            disabilityCategoryInput.replaceChildren(new Option(
                generalDisabilityInput.value ? 'Select disability category' : 'Select general category first',
                '',
            ));
            categories.forEach((category) => disabilityCategoryInput.add(new Option(category, category)));
            disabilityCategoryInput.disabled = isEmployerAccount() || !generalDisabilityInput.value;

            if (categories.includes(selectedValue)) {
                disabilityCategoryInput.value = selectedValue;
            }

            disabilityCategoryInput.dataset.selectedDisabilityCategory = disabilityCategoryInput.value;
            syncRequiredFields();
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

            syncDisabilityCategory();

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
    
        const setAuthRole = (role, { finalizeSelection = false } = {}) => {
            const normalizedRole = role === 'employer' ? 'employer' : 'pwd_applicant';
    
            const changingAccountType = normalizedRole !== getAccountType();
            const currentFormHasData = hasCurrentRoleDraftData();

            if (changingAccountType && currentFormHasData) {
                roleSwitchNotice?.classList.remove('hidden');
                roleSwitchNotice?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                return;
            }
    
            roleSwitchNotice?.classList.add('hidden');
            roleLockedNotice?.classList.add('is-hidden');
    
            if (authRoleInput) {
                authRoleInput.value = normalizedRole;
            }
    
            const showActiveRole = hasChosenRole || finalizeSelection;
            authRoleCards.forEach((roleCard) => {
                const isActive = showActiveRole && roleCard.dataset.authRole === normalizedRole;
    
                roleCard.classList.toggle('is-active', isActive);
                roleCard.setAttribute('aria-pressed', String(isActive));
            });
    
            syncRegisterMode();
            if (finalizeSelection) hasChosenRole = true;
            syncRoleSelectionVisibility();

            if (finalizeSelection) {
                window.requestAnimationFrame(() => {
                    registerForm.querySelector(`[data-register-step="${currentRegisterStep}"] [data-step-required]:not(:disabled)`)?.focus({ preventScroll: false });
                });
            }
        };
    
        const startOfDay = (date = new Date()) => new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const formatDateValue = (date) => [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0'),
        ].join('-');
        const formatDateLabel = (date) => new Intl.DateTimeFormat('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: 'numeric',
        }).format(date);
        const parseBirthdate = (value) => {
            const normalizedValue = value?.trim() ?? '';
            const isoMatch = normalizedValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            const displayMatch = normalizedValue.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            const [, year, month, day] = isoMatch
                ? isoMatch
                : displayMatch
                    ? [null, displayMatch[3], displayMatch[1], displayMatch[2]]
                    : [];

            if (!year || !month || !day) return null;

            const date = new Date(Number(year), Number(month) - 1, Number(day));

            return date.getFullYear() === Number(year)
                && date.getMonth() === Number(month) - 1
                && date.getDate() === Number(day)
                ? date
                : null;
        };

        let calendarViewDate = startOfDay(parseBirthdate(birthdateValueInput?.value) ?? new Date());

        const syncAgeFromBirthdate = () => {
            const birthdate = parseBirthdate(birthdateValueInput?.value);

            if (!birthdate || !ageInput) return;

            const today = startOfDay();
            let age = today.getFullYear() - birthdate.getFullYear();
            const hasBirthdayPassed =
                today.getMonth() > birthdate.getMonth() ||
                (today.getMonth() === birthdate.getMonth() && today.getDate() >= birthdate.getDate());

            if (!hasBirthdayPassed) age -= 1;
            if (age > 0) ageInput.value = String(age);
        };

        const setBirthdate = (date) => {
            if (!birthdateInput || !birthdateValueInput) return;

            birthdateInput.value = formatDateLabel(date);
            birthdateValueInput.value = formatDateValue(date);
            birthdateInput.setCustomValidity('');
            calendarViewDate = startOfDay(date);
            syncAgeFromBirthdate();
        };

        const syncBirthdateFromText = ({ formatDisplay = false } = {}) => {
            if (!birthdateInput || !birthdateValueInput) return;

            const typedValue = birthdateInput.value.trim();

            if (!typedValue) {
                birthdateValueInput.value = '';
                birthdateInput.setCustomValidity('');
                return;
            }

            const date = parseBirthdate(typedValue);

            if (!date) {
                birthdateValueInput.value = '';
                birthdateInput.setCustomValidity('Enter a valid birthdate in MM/DD/YYYY format.');
                return;
            }

            if (date >= startOfDay()) {
                birthdateValueInput.value = '';
                birthdateInput.setCustomValidity('Birthdate must be before today.');
                return;
            }

            birthdateValueInput.value = formatDateValue(date);
            if (formatDisplay) birthdateInput.value = formatDateLabel(date);
            birthdateInput.setCustomValidity('');
            calendarViewDate = startOfDay(date);
            syncAgeFromBirthdate();
        };

        const renderDatePicker = () => {
            if (!datePickerTitle || !datePickerDays) return;

            const year = calendarViewDate.getFullYear();
            const month = calendarViewDate.getMonth();
            const today = startOfDay();
            const selectedValue = birthdateValueInput?.value;
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            datePickerTitle.textContent = new Intl.DateTimeFormat('en-US', {
                month: 'long',
                year: 'numeric',
            }).format(calendarViewDate);
            datePickerPrevious?.removeAttribute('disabled');
            datePickerNext?.toggleAttribute('disabled', year === today.getFullYear() && month === today.getMonth());
            datePickerDays.replaceChildren();

            for (let blankDay = 0; blankDay < firstDay; blankDay += 1) {
                const spacer = document.createElement('span');
                spacer.setAttribute('aria-hidden', 'true');
                datePickerDays.append(spacer);
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = new Date(year, month, day);
                const value = formatDateValue(date);
                const dayButton = document.createElement('button');
                dayButton.type = 'button';
                dayButton.textContent = String(day);
                dayButton.dataset.datePickerDate = value;
                dayButton.setAttribute('aria-label', new Intl.DateTimeFormat('en-US', {
                    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
                }).format(date));
                dayButton.classList.toggle('is-selected', value === selectedValue);
                dayButton.classList.toggle('is-today', value === formatDateValue(today));
                dayButton.disabled = date >= today;
                datePickerDays.append(dayButton);
            }
        };

        const setDatePickerOpen = (isOpen) => {
            if (!datePickerPopover || !datePickerToggle) return;

            datePickerPopover.hidden = !isOpen;
            datePickerToggle.setAttribute('aria-expanded', String(isOpen));
            if (isOpen) renderDatePicker();
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
            card.addEventListener('click', () => setAuthRole(card.dataset.authRole, { finalizeSelection: true }));
        });

        changeRoleButton?.addEventListener('click', () => {
            if (hasCurrentRoleDraftData()) {
                roleLockedNotice?.classList.remove('is-hidden');
                return;
            }

            hasChosenRole = false;
            roleLockedNotice?.classList.add('is-hidden');
            authRoleCards.forEach((roleCard) => {
                roleCard.classList.remove('is-active');
                roleCard.setAttribute('aria-pressed', 'false');
            });
            syncRoleSelectionVisibility();
            roleSelector?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            registerForm.querySelector(`[data-auth-role="${getAccountType()}"]`)?.focus({ preventScroll: true });
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
        birthdateInput?.addEventListener('input', () => syncBirthdateFromText());
        birthdateInput?.addEventListener('blur', () => syncBirthdateFromText({ formatDisplay: true }));
        birthdateInput?.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setDatePickerOpen(true);
            }
            if (event.key === 'Escape') setDatePickerOpen(false);
        });
        datePickerToggle?.addEventListener('click', () => {
            setDatePickerOpen(datePickerPopover?.hidden ?? true);
        });
        datePickerPrevious?.addEventListener('click', () => {
            calendarViewDate = new Date(calendarViewDate.getFullYear(), calendarViewDate.getMonth() - 1, 1);
            renderDatePicker();
        });
        datePickerNext?.addEventListener('click', () => {
            const today = startOfDay();
            const nextMonth = new Date(calendarViewDate.getFullYear(), calendarViewDate.getMonth() + 1, 1);
            if (nextMonth <= new Date(today.getFullYear(), today.getMonth(), 1)) {
                calendarViewDate = nextMonth;
                renderDatePicker();
            }
        });
        datePickerDays?.addEventListener('click', (event) => {
            if (!(event.target instanceof HTMLButtonElement)) return;

            const date = parseBirthdate(event.target.dataset.datePickerDate);
            if (!date) return;

            setBirthdate(date);
            setDatePickerOpen(false);
            birthdateInput?.focus({ preventScroll: true });
        });
        datePickerClear?.addEventListener('click', () => {
            if (birthdateInput) {
                birthdateInput.value = '';
                birthdateInput.setCustomValidity('');
            }
            if (birthdateValueInput) birthdateValueInput.value = '';
            setDatePickerOpen(false);
            birthdateInput?.focus({ preventScroll: true });
        });
        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && datePicker && !datePicker.contains(event.target)) {
                setDatePickerOpen(false);
            }
        });
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
        generalDisabilityInput?.addEventListener('change', () => syncDisabilityCategory({ preserveSelection: false }));
        disabilityCategoryInput?.addEventListener('change', () => {
            disabilityCategoryInput.dataset.selectedDisabilityCategory = disabilityCategoryInput.value;
        });
    
        if (birthdateValueInput?.value) {
            const savedBirthdate = parseBirthdate(birthdateValueInput.value);

            if (savedBirthdate) {
                setBirthdate(savedBirthdate);
            } else if (birthdateInput) {
                birthdateInput.value = birthdateValueInput.value;
                syncBirthdateFromText();
            }
        }
        syncAgeFromBirthdate();
        nameInputs.forEach(sanitizeNameInput);
        sanitizeAgeInput();
        syncPwdIdFileName();
        setAuthRole(authRoleInput?.value);
    }
};
