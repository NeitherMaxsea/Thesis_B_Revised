// Navigation, account dropdown, and notification-menu behavior.
export const initNavigation = () => {
    const navbar = document.getElementById('site-navbar');
    
    if (navbar) {
        const syncNavbar = () => {
            navbar.classList.toggle('is-scrolled', window.scrollY > 12);
        };
    
        syncNavbar();
        window.addEventListener('scroll', syncNavbar, { passive: true });
    }
    
    const dropdown = document.querySelector('[data-dropdown]');
    
    if (dropdown) {
        const button = dropdown.querySelector('[data-dropdown-button]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        const icon = dropdown.querySelector('[data-dropdown-icon]');
    
        const setOpen = (isOpen) => {
            button.setAttribute('aria-expanded', String(isOpen));
            menu.classList.toggle('invisible', !isOpen);
            menu.classList.toggle('opacity-0', !isOpen);
            menu.classList.toggle('opacity-100', isOpen);
            icon.classList.toggle('rotate-180', isOpen);
        };
    
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(button.getAttribute('aria-expanded') !== 'true');
        });
    
        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                setOpen(false);
            }
        });
    
        dropdown.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setOpen(false));
        });
    }
    
    const dashboardNotificationToggle = document.querySelector('[data-dashboard-notification-toggle]');
    const dashboardNotificationMenu = document.querySelector('[data-dashboard-notification-menu]');
    
    if (dashboardNotificationToggle && dashboardNotificationMenu) {
        const setDashboardNotificationsOpen = (isOpen) => {
            dashboardNotificationToggle.setAttribute('aria-expanded', String(isOpen));
            dashboardNotificationMenu.hidden = !isOpen;
        };
    
        dashboardNotificationToggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const isOpen = dashboardNotificationToggle.getAttribute('aria-expanded') !== 'true';
            setDashboardNotificationsOpen(isOpen);

            if (isOpen) {
                window.dispatchEvent(new CustomEvent('notifications:job-applications-cleared'));
            }
        });
    
        document.addEventListener('click', (event) => {
            if (
                event.target instanceof Element &&
                !dashboardNotificationToggle.contains(event.target) &&
                !dashboardNotificationMenu.contains(event.target)
            ) {
                setDashboardNotificationsOpen(false);
            }
        });
    
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setDashboardNotificationsOpen(false);
            }
        });
    }

    const dashboardProfileMenu = document.querySelector('[data-dashboard-profile-menu]');

    if (dashboardProfileMenu) {
        const button = dashboardProfileMenu.querySelector('[data-dashboard-profile-toggle]');
        const options = dashboardProfileMenu.querySelector('[data-dashboard-profile-options]');

        const setDashboardProfileOpen = (isOpen) => {
            button.setAttribute('aria-expanded', String(isOpen));
            options.hidden = !isOpen;
        };

        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setDashboardProfileOpen(button.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('click', (event) => {
            if (event.target instanceof Element && !dashboardProfileMenu.contains(event.target)) {
                setDashboardProfileOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setDashboardProfileOpen(false);
                button.focus();
            }
        });
    }
    
};
