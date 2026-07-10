import {
    BadgeCheck,
    Bell,
    Briefcase,
    BriefcaseBusiness,
    Building2,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    CircleUserRound,
    CircleCheck,
    CircleSlash,
    CircleX,
    ClipboardList,
    CreditCard,
    createIcons,
    Download,
    Eye,
    FilePlus2,
    FileText,
    HardDrive,
    History,
    House,
    Hourglass,
    LayoutDashboard,
    List,
    ListFilter,
    PanelLeftClose,
    Palette,
    ReceiptText,
    Printer,
    Search,
    Settings2,
    ShieldCheck,
    SlidersHorizontal,
    Trash2,
    UserRoundPlus,
    UsersRound,
    WalletCards,
    X,
} from 'lucide';
import Swal from 'sweetalert2';

const adminIcons = {
    BadgeCheck,
    Bell,
    Briefcase,
    BriefcaseBusiness,
    Building2,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    CircleUserRound,
    CircleCheck,
    CircleSlash,
    CircleX,
    ClipboardList,
    CreditCard,
    Download,
    Eye,
    FilePlus2,
    FileText,
    HardDrive,
    History,
    House,
    Hourglass,
    LayoutDashboard,
    List,
    ListFilter,
    PanelLeftClose,
    Palette,
    ReceiptText,
    Printer,
    Search,
    Settings2,
    ShieldCheck,
    SlidersHorizontal,
    Trash2,
    UserRoundPlus,
    UsersRound,
    WalletCards,
    X,
};

// Dashboard-only interactions: compact navigation and expandable menu groups.
export const initAdminDashboard = () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');

    if (!sidebar) {
        return;
    }

    if (sidebar.dataset.adminShellInitialized !== 'true') {
        sidebar.dataset.adminShellInitialized = 'true';

    const collapseButton = sidebar.querySelector('[data-admin-sidebar-toggle]');
    const dashboardShell = sidebar.closest('.admin-dashboard-shell');
    const settingsStorageKey = 'admin-dashboard-settings';
    let dashboardSettings = null;
    const closeSidebarGroups = () => {
        sidebar.querySelectorAll('.admin-nav-cluster').forEach((group) => {
            group.classList.add('is-closed');
            group.querySelector('[data-admin-nav-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };
    const setSidebarCollapsed = (isCollapsed) => {
        sidebar.classList.toggle('is-collapsed', isCollapsed);
        dashboardShell?.classList.toggle('has-collapsed-sidebar', isCollapsed);

        if (isCollapsed) {
            closeSidebarGroups();
        }
        collapseButton?.setAttribute('aria-expanded', String(!isCollapsed));
        collapseButton?.setAttribute(
            'aria-label',
            isCollapsed ? 'Expand sidebar' : 'Collapse sidebar',
        );

        try {
            window.localStorage.setItem('admin-sidebar-collapsed', String(isCollapsed));
        } catch {
            // The sidebar still works when local storage is unavailable.
        }
    };

    try {
        setSidebarCollapsed(window.localStorage.getItem('admin-sidebar-collapsed') === 'true');
    } catch {
        setSidebarCollapsed(false);
    }

    collapseButton?.addEventListener('click', () => {
        const isCollapsed = !sidebar.classList.contains('is-collapsed');

        setSidebarCollapsed(isCollapsed);

        if (dashboardSettings) {
            dashboardSettings.compactNavigation = isCollapsed;
            saveDashboardSettings();
            syncSettingsControls();
        }
    });

    sidebar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-admin-nav-toggle]');

        if (button) {
            const group = button.closest('.admin-nav-cluster');
            const isClosed = group?.classList.toggle('is-closed');

            button.setAttribute('aria-expanded', String(!isClosed));
        }
    });

    const settingsModal = document.querySelector('[data-admin-settings-modal]');
    const settingsOpeners = document.querySelectorAll('[data-admin-settings-open]');
    const settingsDialog = settingsModal?.querySelector('.admin-settings-dialog');
    let settingsTrigger = null;
    const settingControls = settingsModal?.querySelectorAll('[data-admin-setting]') ?? [];
    const savedSidebarState = (() => {
        try {
            return window.localStorage.getItem('admin-sidebar-collapsed') === 'true';
        } catch {
            return false;
        }
    })();
    const defaultDashboardSettings = {
        appearance: 'System',
        contrast: 'Medium',
        accent: 'Green',
        language: 'Auto-detect',
        reduceMotion: false,
        reviewUpdates: true,
        systemNotices: true,
        emailSummaries: false,
        dashboardAnimations: true,
        compactNavigation: savedSidebarState,
        density: 'Comfortable',
    };

    const saveDashboardSettings = () => {
        try {
            window.localStorage.setItem(settingsStorageKey, JSON.stringify(dashboardSettings));
        } catch {
            // Settings remain available for this page when local storage is unavailable.
        }
    };

    const syncSettingsControls = () => {
        settingControls.forEach((control) => {
            const key = control.dataset.adminSetting;

            if (!key || !dashboardSettings) {
                return;
            }

            if (control instanceof HTMLInputElement && control.type === 'checkbox') {
                control.checked = Boolean(dashboardSettings[key]);
            } else if (control instanceof HTMLSelectElement) {
                control.value = String(dashboardSettings[key]);
            }
        });
    };

    const applyDashboardSettings = () => {
        if (!dashboardSettings) {
            return;
        }

        const systemPrefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false;
        const usesDarkTheme = dashboardSettings.appearance === 'Dark'
            || (dashboardSettings.appearance === 'System' && systemPrefersDark);

        document.body.classList.toggle('admin-theme-dark', usesDarkTheme);
        document.body.classList.toggle('admin-contrast-high', dashboardSettings.contrast === 'High');
        document.body.classList.toggle('admin-accent-purple', dashboardSettings.accent === 'Purple');
        document.body.classList.toggle('admin-accent-blue', dashboardSettings.accent === 'Blue');
        document.body.classList.toggle('admin-motion-reduced', dashboardSettings.reduceMotion);
        document.body.classList.toggle('admin-animations-off', !dashboardSettings.dashboardAnimations);
        document.body.classList.toggle('admin-density-compact', dashboardSettings.density === 'Compact');
        document.body.classList.toggle('admin-density-spacious', dashboardSettings.density === 'Spacious');

        document.documentElement.lang = dashboardSettings.language === 'Filipino'
            ? 'fil'
            : dashboardSettings.language === 'English'
                ? 'en'
                : (navigator.language || 'en').split('-')[0];

        setSidebarCollapsed(Boolean(dashboardSettings.compactNavigation));
    };

    try {
        const storedSettings = JSON.parse(window.localStorage.getItem(settingsStorageKey) || '{}');
        dashboardSettings = { ...defaultDashboardSettings, ...storedSettings };
    } catch {
        dashboardSettings = { ...defaultDashboardSettings };
    }

    syncSettingsControls();
    applyDashboardSettings();

    settingControls.forEach((control) => {
        control.addEventListener('change', () => {
            const key = control.dataset.adminSetting;

            if (!key || !dashboardSettings) {
                return;
            }

            dashboardSettings[key] = control instanceof HTMLInputElement && control.type === 'checkbox'
                ? control.checked
                : control.value;
            saveDashboardSettings();
            applyDashboardSettings();
            syncSettingsControls();
        });
    });

    window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener?.('change', () => {
        if (dashboardSettings?.appearance === 'System') {
            applyDashboardSettings();
        }
    });

    const closeSettings = () => {
        if (!settingsModal || settingsModal.hidden) {
            return;
        }

        settingsModal.classList.remove('is-open');
        settingsModal.hidden = true;
        document.body.classList.remove('admin-settings-is-open');
        settingsTrigger?.focus();
    };

    const openSettings = (trigger) => {
        if (!settingsModal) {
            return;
        }

        settingsTrigger = trigger;
        settingsModal.hidden = false;
        document.body.classList.add('admin-settings-is-open');
        window.requestAnimationFrame(() => settingsModal.classList.add('is-open'));
        settingsDialog?.focus();
    };

    settingsOpeners.forEach((button) => {
        button.addEventListener('click', () => openSettings(button));
    });

    settingsModal?.querySelectorAll('[data-admin-settings-close]').forEach((button) => {
        button.addEventListener('click', closeSettings);
    });

    settingsModal?.querySelectorAll('[data-admin-settings-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const selectedPanel = tab.dataset.adminSettingsTab;

            settingsModal.querySelectorAll('[data-admin-settings-tab]').forEach((button) => {
                const isSelected = button === tab;
                button.classList.toggle('is-active', isSelected);
                button.setAttribute('aria-selected', String(isSelected));
            });

            settingsModal.querySelectorAll('[data-admin-settings-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.adminSettingsPanel !== selectedPanel;
            });
        });
    });

    const notificationRoot = document.querySelector('[data-admin-notifications]');
    const notificationToggle = notificationRoot?.querySelector('[data-admin-notifications-toggle]');
    const notificationPanel = notificationRoot?.querySelector('[data-admin-notification-panel]');
    const notificationBadge = notificationRoot?.querySelector('[data-admin-notification-badge]');
    const notificationList = notificationRoot?.querySelector('[data-admin-notification-list]');
    let notificationCursor = new Date().toISOString();
    let unreadNotifications = 0;

    const renderNotifications = (notifications = []) => {
        if (!notificationList) {
            return;
        }

        const fragment = document.createDocumentFragment();

        if (!notifications.length) {
            const empty = document.createElement('p');
            empty.className = 'admin-notification-empty';
            empty.textContent = 'No account activity yet.';
            fragment.append(empty);
        } else {
            notifications.forEach((notification) => {
                const item = document.createElement('article');
                const title = document.createElement('strong');
                const message = document.createElement('span');
                const time = document.createElement('small');

                title.textContent = notification.name;
                message.textContent = `New ${notification.role.toLowerCase()} account`;
                time.textContent = notification.created_at || 'Just now';
                item.append(title, message, time);
                fragment.append(item);
            });
        }

        notificationList.replaceChildren(fragment);
    };

    const updateNotifications = async () => {
        const endpoint = notificationRoot?.dataset.notificationUrl;

        if (!endpoint) {
            return;
        }

        try {
            const response = await fetch(`${endpoint}?since=${encodeURIComponent(notificationCursor)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            renderNotifications(payload.notifications);
            notificationCursor = payload.now || new Date().toISOString();

            if (payload.new_count > 0) {
                unreadNotifications += payload.new_count;
                if (notificationBadge) {
                    notificationBadge.textContent = String(unreadNotifications);
                    notificationBadge.hidden = false;
                }
            }
        } catch {
            // Keep the last successful notification list when a poll is unavailable.
        }
    };

    notificationToggle?.addEventListener('click', () => {
        if (!notificationPanel) {
            return;
        }

        const isOpening = notificationPanel.hidden;
        notificationPanel.hidden = !isOpening;
        notificationToggle.setAttribute('aria-expanded', String(isOpening));

        if (isOpening) {
            unreadNotifications = 0;
            if (notificationBadge) {
                notificationBadge.hidden = true;
            }
            updateNotifications();
        }
    });

    document.addEventListener('click', (event) => {
        if (notificationRoot && notificationPanel && !notificationRoot.contains(event.target)) {
            notificationPanel.hidden = true;
            notificationToggle?.setAttribute('aria-expanded', 'false');
        }
    });

    updateNotifications();
    window.setInterval(updateNotifications, 10000);

    document.querySelector('[data-admin-logout-open]')?.addEventListener('click', async () => {
        const result = await Swal.fire({
            icon: 'question',
            title: 'Leave the admin workspace?',
            text: 'You will be signed out and returned to the login screen.',
            showCancelButton: true,
            confirmButtonText: 'Yes, log out',
            cancelButtonText: 'No, stay here',
            reverseButtons: true,
            customClass: {
                popup: 'admin-swal-popup',
                confirmButton: 'admin-swal-confirm',
                cancelButton: 'admin-swal-cancel',
            },
        });

        if (result.isConfirmed) {
            document.getElementById('admin-logout-form')?.requestSubmit();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSettings();
        }
    });

        document.addEventListener('admin:content-replaced', () => initAdminDashboard());
    }

    const usersOverview = document.querySelector('[data-admin-users-overview]');

    if (usersOverview) {
        const userSearch = usersOverview.querySelector('[data-admin-user-search]');
        const roleFilter = usersOverview.querySelector('[data-admin-user-role]');
        const statusFilter = usersOverview.querySelector('[data-admin-user-status]');
        const userRows = [...usersOverview.querySelectorAll('[data-admin-user-row]')];
        const userCount = usersOverview.querySelector('[data-admin-user-count]');
        const normalizeValue = (value = '') => value.trim().toLocaleLowerCase();

        const filterUsers = () => {
            const searchTerm = normalizeValue(userSearch?.value);
            const selectedRole = normalizeValue(roleFilter?.value || 'all');
            const selectedStatus = normalizeValue(statusFilter?.value || 'all');
            let visibleRows = 0;

            userRows.forEach((row) => {
                const isVisible = (!searchTerm || (row.dataset.search || '').includes(searchTerm))
                    && (selectedRole === 'all' || row.dataset.role === selectedRole)
                    && (selectedStatus === 'all' || row.dataset.status === selectedStatus);

                row.hidden = !isVisible;
                visibleRows += Number(isVisible);
            });

            if (userCount) {
                userCount.textContent = `${visibleRows} of ${userRows.length} users`;
            }
        };

        userSearch?.addEventListener('input', filterUsers);
        roleFilter?.addEventListener('change', filterUsers);
        statusFilter?.addEventListener('change', filterUsers);

        usersOverview.querySelector('[data-admin-user-export]')?.addEventListener('click', () => {
            const headers = ['ID', 'User', 'Email', 'Role', 'Registration Type', 'Date'];
            const records = userRows
                .filter((row) => !row.hidden)
                .map((row) => {
                    const cells = row.querySelectorAll('td');
                    const account = cells[1]?.querySelector('strong')?.textContent?.trim() || '';
                    const email = cells[1]?.querySelector('small')?.textContent?.trim() || '';

                    return [
                        cells[0]?.textContent?.trim() || '',
                        account,
                        email,
                        cells[2]?.textContent?.trim() || '',
                        cells[3]?.textContent?.trim() || '',
                        cells[4]?.textContent?.trim() || '',
                    ];
                });
            const escapeCell = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;');
            const spreadsheet = `<table><thead><tr>${headers.map((header) => `<th>${escapeCell(header)}</th>`).join('')}</tr></thead><tbody>${records.map((record) => `<tr>${record.map((cell) => `<td>${escapeCell(cell)}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
            const blob = new Blob([spreadsheet], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = `user-overview-${new Date().toISOString().slice(0, 10)}.xls`;
            link.click();
            URL.revokeObjectURL(url);
        });

        usersOverview.querySelector('[data-admin-user-print]')?.addEventListener('click', () => window.print());
        filterUsers();
    }

    const createUser = document.querySelector('[data-admin-create-user]');

    if (createUser) {
        const createTabs = createUser.querySelectorAll('[data-admin-create-tab]');
        const createPanels = createUser.querySelectorAll('[data-admin-create-panel]');

        createTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const selectedPanel = tab.dataset.adminCreateTab;

                createTabs.forEach((button) => {
                    const isSelected = button === tab;
                    button.classList.toggle('is-active', isSelected);
                    button.setAttribute('aria-selected', String(isSelected));
                });
                createPanels.forEach((panel) => {
                    panel.hidden = panel.dataset.adminCreatePanel !== selectedPanel;
                });
            });
        });
    }

    const applicantList = document.querySelector('[data-admin-applicant-list]');

    if (applicantList) {
        const applicantDrawer = applicantList.querySelector('[data-admin-applicant-drawer]');
        const applicantDrawerPanel = applicantDrawer?.querySelector('.admin-applicant-drawer__panel');
        const applicantEditForm = applicantDrawer?.querySelector('[data-admin-applicant-edit-form]');
        const applicantViewContent = applicantDrawer?.querySelector('[data-admin-applicant-view-content]');
        const applicantViewActions = applicantDrawer?.querySelector('[data-admin-applicant-view-actions]');
        const applicantEditActions = applicantDrawer?.querySelector('[data-admin-applicant-edit-actions]');
        const applicantEditError = applicantDrawer?.querySelector('[data-admin-applicant-edit-error]');
        let applicantTrigger = null;
        let selectedApplicantRow = null;

        const toDataKey = (key = '') => key.replace(/-([a-z])/g, (_, letter) => letter.toUpperCase());
        const cleanInputValue = (value = '') => value === 'Not set' ? '' : value;

        const populateApplicantDetails = (button) => {
            applicantDrawer?.querySelectorAll('[data-admin-applicant-detail]').forEach((element) => {
                const dataKey = toDataKey(element.dataset.adminApplicantDetail);

                element.textContent = button.dataset[dataKey] || 'Not set';
            });

            const documentLink = applicantDrawer?.querySelector('[data-admin-applicant-document]');

            if (documentLink) {
                const documentUrl = button.dataset.pwdIdUrl || '';
                documentLink.hidden = documentUrl === '';
                documentLink.href = documentUrl || '#';
            }
        };

        const syncApplicantEditForm = () => {
            if (!applicantEditForm || !applicantTrigger) {
                return;
            }

            ['name', 'email', 'contact', 'disability', 'birthdate', 'age', 'address'].forEach((key) => {
                const inputName = key === 'contact' ? 'contact_number'
                    : key === 'address' ? 'street_address'
                        : key;
                const input = applicantEditForm.elements.namedItem(inputName);

                if (input instanceof HTMLInputElement) {
                    input.value = cleanInputValue(applicantTrigger.dataset[key] || '');
                }
            });
        };

        const showApplicantView = () => {
            applicantViewContent && (applicantViewContent.hidden = false);
            applicantViewActions && (applicantViewActions.hidden = false);
            applicantEditForm && (applicantEditForm.hidden = true);
            applicantEditActions && (applicantEditActions.hidden = true);
            if (applicantEditError) {
                applicantEditError.hidden = true;
                applicantEditError.textContent = '';
            }
        };

        const openApplicantDrawer = (button) => {
            if (!applicantDrawer) {
                return;
            }

            selectedApplicantRow?.classList.remove('is-selected');
            selectedApplicantRow = button.closest('[data-admin-applicant-row]');
            selectedApplicantRow?.classList.add('is-selected');
            applicantTrigger = button;
            showApplicantView();
            populateApplicantDetails(button);
            syncApplicantEditForm();
            applicantDrawer.hidden = false;
            document.body.classList.add('admin-applicant-drawer-open');
            window.requestAnimationFrame(() => applicantDrawer.classList.add('is-open'));
            applicantDrawerPanel?.focus();
        };

        const closeApplicantDrawer = () => {
            if (!applicantDrawer || applicantDrawer.hidden) {
                return;
            }

            applicantDrawer.classList.remove('is-open');
            window.setTimeout(() => {
                applicantDrawer.hidden = true;
                document.body.classList.remove('admin-applicant-drawer-open');
                selectedApplicantRow?.classList.remove('is-selected');
                selectedApplicantRow = null;
                applicantTrigger?.focus();
            }, 220);
        };

        applicantList.querySelectorAll('[data-admin-applicant-open]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                openApplicantDrawer(button);
            });
        });

        applicantList.querySelectorAll('[data-admin-applicant-row]').forEach((row) => {
            const openButton = row.querySelector('[data-admin-applicant-open]');

            row.addEventListener('click', () => openButton && openApplicantDrawer(openButton));
            row.addEventListener('keydown', (event) => {
                if ((event.key === 'Enter' || event.key === ' ') && openButton) {
                    event.preventDefault();
                    openApplicantDrawer(openButton);
                }
            });
        });

        applicantDrawer?.querySelector('[data-admin-applicant-edit-open]')?.addEventListener('click', () => {
            syncApplicantEditForm();
            applicantViewContent && (applicantViewContent.hidden = true);
            applicantViewActions && (applicantViewActions.hidden = true);
            applicantEditForm && (applicantEditForm.hidden = false);
            applicantEditActions && (applicantEditActions.hidden = false);
            applicantEditForm?.elements.namedItem('name')?.focus();
        });

        applicantDrawer?.querySelector('[data-admin-applicant-edit-cancel]')?.addEventListener('click', showApplicantView);

        applicantEditForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!applicantTrigger || !applicantEditForm.checkValidity()) {
                applicantEditForm.reportValidity();
                return;
            }

            const saveButton = applicantDrawer?.querySelector('[data-admin-applicant-edit-save]');
            const requestUrl = applicantTrigger.dataset.updateUrl;

            if (!requestUrl) {
                return;
            }

            saveButton && (saveButton.disabled = true);
            applicantDrawerPanel?.classList.add('is-saving');

            try {
                const response = await fetch(requestUrl, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: new URLSearchParams(new FormData(applicantEditForm)),
                });
                const payload = await response.json();

                if (!response.ok) {
                    const error = payload.errors ? Object.values(payload.errors).flat()[0] : payload.message;
                    throw new Error(error || 'Unable to save this applicant.');
                }

                const values = payload.applicant;
                Object.entries(values).forEach(([key, value]) => {
                    const dataKey = key === 'contact' ? 'contact'
                        : key === 'address' ? 'address'
                            : key;
                    applicantTrigger.dataset[dataKey] = value;
                });
                populateApplicantDetails(applicantTrigger);
                syncApplicantEditForm();

                if (selectedApplicantRow) {
                    const cells = selectedApplicantRow.querySelectorAll('td');
                    const identityName = cells[0]?.querySelector('strong');
                    const identityEmail = cells[0]?.querySelector('small');
                    if (identityName) identityName.textContent = values.name;
                    if (identityEmail) identityEmail.textContent = values.email;
                    if (cells[1]) cells[1].textContent = values.contact;
                    if (cells[2]) cells[2].textContent = values.disability;
                    if (cells[3]) cells[3].textContent = values.age;
                }

                showApplicantView();
                await Swal.fire({
                    icon: 'success',
                    title: 'Applicant updated',
                    text: payload.message,
                    confirmButtonText: 'Done',
                    customClass: { popup: 'admin-swal-popup', confirmButton: 'admin-swal-confirm' },
                });
            } catch (error) {
                if (applicantEditError) {
                    applicantEditError.textContent = error instanceof Error ? error.message : 'Unable to save this applicant.';
                    applicantEditError.hidden = false;
                }
            } finally {
                saveButton && (saveButton.disabled = false);
                applicantDrawerPanel?.classList.remove('is-saving');
            }
        });

        applicantDrawer?.querySelectorAll('[data-admin-applicant-close]').forEach((button) => {
            button.addEventListener('click', closeApplicantDrawer);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeApplicantDrawer();
            }
        });
    }

    createIcons({ icons: adminIcons, attrs: { 'aria-hidden': 'true', focusable: 'false' } });
};
