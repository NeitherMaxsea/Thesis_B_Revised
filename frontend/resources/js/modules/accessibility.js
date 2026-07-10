import i18next from 'i18next';

// Accessibility controls are separate so changes here cannot affect account-role workflows.
export const initAccessibilityWidget = ({ applyTranslations, getCurrentInterfaceLanguage }) => {
    const accessibilityWidget = document.querySelector('[data-accessibility]');
    
    if (accessibilityWidget) {
        const storageKey = 'pwd-accessibility-settings';
        const toggleButton = accessibilityWidget.querySelector('[data-accessibility-toggle]');
        const closeButton = accessibilityWidget.querySelector('[data-accessibility-close]');
        const panel = accessibilityWidget.querySelector('[data-accessibility-panel]');
        const darkInput = accessibilityWidget.querySelector('[data-accessibility-dark]');
        const dyslexiaInput = accessibilityWidget.querySelector('[data-accessibility-dyslexia]');
        const contrastInput = accessibilityWidget.querySelector('[data-accessibility-contrast]');
        const choiceButtons = accessibilityWidget.querySelectorAll('[data-accessibility-choice]');
        const resetButton = accessibilityWidget.querySelector('[data-accessibility-reset]');
        const voiceTextButton = accessibilityWidget.querySelector('[data-voice-text]');
        const voiceTypeButton = accessibilityWidget.querySelector('[data-voice-type]');
        const voiceStatus = accessibilityWidget.querySelector('[data-voice-status]');
        const voiceOutput = accessibilityWidget.querySelector('[data-voice-output]');
        const SpeechRecognitionConstructor = window.SpeechRecognition || window.webkitSpeechRecognition;
        const supportsSpeechRecognition = typeof SpeechRecognitionConstructor === 'function';
    
        const optionValues = {
            font: {
                small: 0.95,
                normal: 1,
                large: 1.15,
            },
            saturation: {
                low: 0.7,
                normal: 1,
                high: 1.3,
            },
            language: {
                en: 'en',
                fil: 'fil',
                ceb: 'ceb',
                ilo: 'ilo',
            },
        };
    
        const defaultSettings = {
            dark: false,
            dyslexia: false,
            font: 'normal',
            contrast: false,
            saturation: 'normal',
            language: 'en',
        };
    
        const normalizeChoice = (key, value) => {
            if (Object.hasOwn(optionValues[key], value)) {
                return value;
            }
    
            if (typeof value === 'number') {
                if (value < 100) {
                    return 'low' in optionValues[key] ? 'low' : 'small';
                }
    
                if (value > 100) {
                    return 'high' in optionValues[key] ? 'high' : 'large';
                }
            }
    
            return key === 'language' ? 'en' : 'normal';
        };
    
        const normalizeToggle = (value) => value === true || value === 'true' || value === 'high';
    
        const readSettings = () => {
            try {
                const savedSettings = { ...defaultSettings, ...JSON.parse(window.localStorage.getItem(storageKey)) };
    
                return {
                    ...savedSettings,
                    font: normalizeChoice('font', savedSettings.font),
                    contrast: normalizeToggle(savedSettings.contrast),
                    saturation: normalizeChoice('saturation', savedSettings.saturation),
                    language: normalizeChoice('language', savedSettings.language),
                };
            } catch {
                return { ...defaultSettings };
            }
        };
    
        let accessibilitySettings = readSettings();
        let speechRecognition = null;
        let isVoiceListening = false;
        let voiceMode = 'text';
        let voiceBaseTranscript = '';
        let typedTranscript = '';
        let lastVoiceTarget = null;
    
        const saveSettings = () => {
            try {
                window.localStorage.setItem(storageKey, JSON.stringify(accessibilitySettings));
            } catch {
                // Storage can fail in private browsing; controls should still work for this page view.
            }
        };
    
        const syncSwitchControl = (input, isChecked) => {
            input.checked = isChecked;
            input.setAttribute('aria-checked', String(isChecked));
    
            const state = input.closest('.accessibility-switch')?.querySelector('[data-accessibility-switch-state]');
    
            if (state) {
                const stateLabel = isChecked ? 'On' : 'Off';
                state.textContent = i18next.t(stateLabel, { defaultValue: stateLabel });
            }
        };
    
        const syncAccessibilityControls = () => {
            syncSwitchControl(darkInput, accessibilitySettings.dark);
            syncSwitchControl(dyslexiaInput, accessibilitySettings.dyslexia);
            syncSwitchControl(contrastInput, accessibilitySettings.contrast);
    
            choiceButtons.forEach((button) => {
                const key = button.dataset.accessibilityChoice;
                const isActive = accessibilitySettings[key] === button.dataset.value;
    
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });
        };
    
        const applyAccessibilitySettings = () => {
            document.documentElement.style.setProperty('--accessibility-font-scale', String(optionValues.font[accessibilitySettings.font]));
            document.documentElement.style.setProperty('--accessibility-saturation', String(optionValues.saturation[accessibilitySettings.saturation]));
            document.body.classList.toggle('accessibility-dark', accessibilitySettings.dark);
            document.body.classList.toggle('accessibility-dyslexia', accessibilitySettings.dyslexia);
            document.body.classList.toggle('accessibility-invert', accessibilitySettings.contrast);
            applyTranslations(accessibilitySettings.language);
            syncAccessibilityControls();
            saveSettings();
        };
    
        const setVoiceStatus = (message) => {
            if (voiceStatus) {
                voiceStatus.textContent = message;
            }
        };
    
        const isVoiceTypingTarget = (element) => {
            if (!(element instanceof HTMLElement)) {
                return false;
            }
    
            if (element.isContentEditable) {
                return true;
            }
    
            if (element instanceof HTMLTextAreaElement) {
                return !element.disabled && !element.readOnly;
            }
    
            if (!(element instanceof HTMLInputElement)) {
                return false;
            }
    
            const blockedTypes = new Set([
                'button',
                'checkbox',
                'color',
                'date',
                'file',
                'hidden',
                'image',
                'month',
                'number',
                'password',
                'radio',
                'range',
                'reset',
                'submit',
                'time',
                'week',
            ]);
    
            return !blockedTypes.has(element.type) && !element.disabled && !element.readOnly;
        };
    
        const insertVoiceText = (target, text) => {
            if (!target || !text.trim()) {
                return;
            }
    
            const textToInsert = text.trim();
    
            if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
                const start = target.selectionStart ?? target.value.length;
                const end = target.selectionEnd ?? target.value.length;
                const prefix = target.value.slice(0, start);
                const suffix = target.value.slice(end);
                const spacer = prefix && !/\s$/.test(prefix) ? ' ' : '';
                const trailingSpacer = suffix && !/^\s/.test(suffix) ? ' ' : '';
    
                target.value = `${prefix}${spacer}${textToInsert}${trailingSpacer}${suffix}`;
                const cursorPosition = (prefix + spacer + textToInsert).length;
                target.setSelectionRange(cursorPosition, cursorPosition);
                target.dispatchEvent(new Event('input', { bubbles: true }));
                target.dispatchEvent(new Event('change', { bubbles: true }));
                target.focus({ preventScroll: true });
                return;
            }
    
            if (target.isContentEditable) {
                target.focus({ preventScroll: true });
                document.execCommand('insertText', false, textToInsert);
                target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };
    
        const syncVoiceButtons = () => {
            voiceTextButton?.classList.toggle('is-listening', isVoiceListening && voiceMode === 'text');
            voiceTypeButton?.classList.toggle('is-listening', isVoiceListening && voiceMode === 'type');
            voiceTextButton?.setAttribute('aria-pressed', String(isVoiceListening && voiceMode === 'text'));
            voiceTypeButton?.setAttribute('aria-pressed', String(isVoiceListening && voiceMode === 'type'));
        };
    
        const getVoiceLanguage = () => {
            const languageMap = {
                en: 'en-US',
                fil: 'fil-PH',
                ceb: 'en-PH',
                ilo: 'en-PH',
            };
    
            return languageMap[getCurrentInterfaceLanguage()] ?? 'en-US';
        };
    
        const getSpeechRecognition = () => {
            if (!supportsSpeechRecognition) {
                return null;
            }
    
            if (speechRecognition) {
                return speechRecognition;
            }
    
            speechRecognition = new SpeechRecognitionConstructor();
            speechRecognition.continuous = false;
            speechRecognition.interimResults = true;
    
            speechRecognition.onstart = () => {
                isVoiceListening = true;
                setVoiceStatus('Listening');
                syncVoiceButtons();
            };
    
            speechRecognition.onend = () => {
                isVoiceListening = false;
                setVoiceStatus('Ready');
                syncVoiceButtons();
            };
    
            speechRecognition.onerror = () => {
                isVoiceListening = false;
                setVoiceStatus('Mic unavailable');
                syncVoiceButtons();
            };
    
            speechRecognition.onresult = (event) => {
                let finalTranscript = '';
                let interimTranscript = '';
    
                for (let index = 0; index < event.results.length; index += 1) {
                    const transcript = event.results[index][0]?.transcript.trim() ?? '';
    
                    if (!transcript) {
                        continue;
                    }
    
                    if (event.results[index].isFinal) {
                        finalTranscript = `${finalTranscript} ${transcript}`.trim();
                    } else {
                        interimTranscript = `${interimTranscript} ${transcript}`.trim();
                    }
                }
    
                if (voiceMode === 'type') {
                    const newText = finalTranscript.slice(typedTranscript.length).trim();
    
                    if (newText) {
                        insertVoiceText(lastVoiceTarget, newText);
                        typedTranscript = finalTranscript;
                    }
    
                    if (voiceOutput) {
                        voiceOutput.value = [typedTranscript, interimTranscript].filter(Boolean).join(' ');
                    }
    
                    return;
                }
    
                if (voiceOutput) {
                    voiceOutput.value = [voiceBaseTranscript, finalTranscript, interimTranscript]
                        .filter(Boolean)
                        .join(' ')
                        .trim();
                }
            };
    
            return speechRecognition;
        };
    
        const startVoiceInput = (mode) => {
            const recognition = getSpeechRecognition();
    
            if (!recognition) {
                setVoiceStatus('Not supported');
                return;
            }
    
            if (isVoiceListening) {
                recognition.stop();
    
                if (voiceMode === mode) {
                    return;
                }
            }
    
            voiceMode = mode;
            voiceBaseTranscript = voiceOutput?.value.trim() ?? '';
            typedTranscript = '';
    
            if (mode === 'type') {
                const activeElement = document.activeElement;
    
                if (isVoiceTypingTarget(activeElement)) {
                    lastVoiceTarget = activeElement;
                }
    
                if (!lastVoiceTarget) {
                    setVoiceStatus('Focus field first');
                    return;
                }
            }
    
            recognition.lang = getVoiceLanguage();
    
            try {
                recognition.start();
            } catch {
                setVoiceStatus('Try again');
            }
        };
    
        const setAccessibilityPanel = (isOpen) => {
            toggleButton.setAttribute('aria-expanded', String(isOpen));
    
            if (isOpen) {
                panel.hidden = false;
                window.requestAnimationFrame(() => panel.classList.add('is-open'));
                return;
            }
    
            panel.classList.remove('is-open');
            window.setTimeout(() => {
                if (toggleButton.getAttribute('aria-expanded') !== 'true') {
                    panel.hidden = true;
                }
            }, 180);
        };
    
        toggleButton.addEventListener('click', () => {
            setAccessibilityPanel(toggleButton.getAttribute('aria-expanded') !== 'true');
        });
    
        closeButton.addEventListener('click', () => setAccessibilityPanel(false));
    
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setAccessibilityPanel(false);
            }
        });
    
        document.addEventListener('click', (event) => {
            if (!accessibilityWidget.contains(event.target)) {
                setAccessibilityPanel(false);
            }
        });
    
        document.addEventListener('focusin', (event) => {
            if (isVoiceTypingTarget(event.target)) {
                lastVoiceTarget = event.target;
            }
        });
    
        if (!supportsSpeechRecognition) {
            voiceTextButton?.setAttribute('disabled', 'disabled');
            voiceTypeButton?.setAttribute('disabled', 'disabled');
            setVoiceStatus('Not supported');
        }
    
        voiceTextButton?.addEventListener('click', () => startVoiceInput('text'));
        voiceTypeButton?.addEventListener('click', () => startVoiceInput('type'));
    
        darkInput.addEventListener('change', () => {
            accessibilitySettings.dark = darkInput.checked;
            applyAccessibilitySettings();
        });
    
        dyslexiaInput.addEventListener('change', () => {
            accessibilitySettings.dyslexia = dyslexiaInput.checked;
            applyAccessibilitySettings();
        });
    
        contrastInput.addEventListener('change', () => {
            accessibilitySettings.contrast = contrastInput.checked;
            applyAccessibilitySettings();
        });
    
        choiceButtons.forEach((button) => {
            button.addEventListener('click', () => {
                accessibilitySettings[button.dataset.accessibilityChoice] = button.dataset.value;
                applyAccessibilitySettings();
            });
        });
    
        resetButton.addEventListener('click', () => {
            accessibilitySettings = { ...defaultSettings };
            applyAccessibilitySettings();
        });
    
        applyAccessibilitySettings();
    }
    
};
