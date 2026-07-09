<div class="accessibility-widget" data-accessibility>
    <button
        type="button"
        class="accessibility-trigger"
        data-accessibility-toggle
        aria-expanded="false"
        aria-controls="accessibility-panel"
        aria-label="Open accessibility settings"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 7.25h9.5M17.25 7.25h2M15.25 5.25v4M4.75 12h2M9.75 12h9.5M7.75 10v4M4.75 16.75h8M16.25 16.75h3M14.25 14.75v4" />
        </svg>
    </button>

    <div id="accessibility-panel" class="accessibility-panel" data-accessibility-panel hidden>
        <div class="accessibility-panel__header">
            <h2>Accessibility</h2>
            <button type="button" data-accessibility-close aria-label="Close accessibility settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <label class="accessibility-switch">
            <span>
                <strong>Dark Mode</strong>
            </span>
            <span class="accessibility-switch__control">
                <input type="checkbox" role="switch" data-accessibility-dark>
                <span class="accessibility-switch__track" aria-hidden="true">
                    <span class="accessibility-switch__thumb"></span>
                </span>
                <span class="accessibility-switch__state" data-accessibility-switch-state>Off</span>
            </span>
        </label>

        <label class="accessibility-switch">
            <span>
                <strong>Dyslexia Support</strong>
            </span>
            <span class="accessibility-switch__control">
                <input type="checkbox" role="switch" data-accessibility-dyslexia>
                <span class="accessibility-switch__track" aria-hidden="true">
                    <span class="accessibility-switch__thumb"></span>
                </span>
                <span class="accessibility-switch__state" data-accessibility-switch-state>Off</span>
            </span>
        </label>

        <div class="accessibility-choice">
            <strong>Font Size</strong>
            <div class="accessibility-choice__options" role="group" aria-label="Font size">
                <button type="button" data-accessibility-choice="font" data-value="small">Small</button>
                <button type="button" data-accessibility-choice="font" data-value="normal">Normal</button>
                <button type="button" data-accessibility-choice="font" data-value="large">Large</button>
            </div>
        </div>

        <label class="accessibility-switch">
            <span>
                <strong>Contrast</strong>
            </span>
            <span class="accessibility-switch__control">
                <input type="checkbox" role="switch" data-accessibility-contrast>
                <span class="accessibility-switch__track" aria-hidden="true">
                    <span class="accessibility-switch__thumb"></span>
                </span>
                <span class="accessibility-switch__state" data-accessibility-switch-state>Off</span>
            </span>
        </label>

        <div class="accessibility-choice">
            <strong>Saturation</strong>
            <div class="accessibility-choice__options" role="group" aria-label="Saturation">
                <button type="button" data-accessibility-choice="saturation" data-value="low">Low</button>
                <button type="button" data-accessibility-choice="saturation" data-value="normal">Normal</button>
                <button type="button" data-accessibility-choice="saturation" data-value="high">High</button>
            </div>
        </div>

        <div class="accessibility-choice">
            <strong>Translation</strong>
            <div class="accessibility-choice__options accessibility-choice__options--language" role="group" aria-label="Translation language">
                <button type="button" data-accessibility-choice="language" data-value="en">English</button>
                <button type="button" data-accessibility-choice="language" data-value="fil">Filipino</button>
                <button type="button" data-accessibility-choice="language" data-value="ceb">Bisaya</button>
                <button type="button" data-accessibility-choice="language" data-value="ilo">Ilocano</button>
            </div>
        </div>

        <div class="accessibility-voice" data-accessibility-voice>
            <div class="accessibility-voice__header">
                <strong>Voice Input</strong>
                <span data-voice-status>Ready</span>
            </div>
            <div class="accessibility-voice__actions">
                <button type="button" data-voice-text aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.15" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 13.75a3 3 0 0 0 3-3v-3.5a3 3 0 1 0-6 0v3.5a3 3 0 0 0 3 3ZM5.75 10.75a6.25 6.25 0 0 0 12.5 0M12 17v3.25M9.5 20.25h5" />
                    </svg>
                    <span>Voice to Text</span>
                </button>
                <button type="button" data-voice-type aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.15" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 6.75h13.5A1.75 1.75 0 0 1 20.5 8.5v7A1.75 1.75 0 0 1 18.75 17.25H5.25A1.75 1.75 0 0 1 3.5 15.5v-7a1.75 1.75 0 0 1 1.75-1.75ZM6.75 10h.01M9.75 10h.01M12.75 10h.01M15.75 10h.01M8.25 13.75h7.5" />
                    </svg>
                    <span>Voice to Type</span>
                </button>
            </div>
            <textarea data-voice-output rows="3" placeholder="Transcript" readonly></textarea>
        </div>

        <button type="button" class="accessibility-reset" data-accessibility-reset>Reset Settings</button>
    </div>
</div>
