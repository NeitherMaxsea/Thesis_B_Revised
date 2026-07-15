// Shared navigation loading state. This is intentionally role-neutral.
export const initPageTransitions = () => {
    const pageLoader = document.getElementById('page-loader');
    const authPaths = new Set(['/login', '/register', '/register/verification']);
    const hasInitialEntryAnimation = document.body.classList.contains('page-entering');
    
    const normalizePath = (pathname) => {
        const normalizedPath = pathname.replace(/\/+$/, '');
    
        return normalizedPath || '/';
    };
    
    const isAuthPath = (pathname) => authPaths.has(normalizePath(pathname));
    const isAdminPath = (pathname) => normalizePath(pathname).startsWith('/admin');
    const isWorkspacePath = (pathname) => /^\/(?:applicant\/(?:dashboard|profile|review)|employer\/(?:dashboard|profile)|messages)$/.test(normalizePath(pathname));
    let adminNavigationInProgress = false;
    let workspaceNavigationInProgress = false;

    const navigateAdminPage = async (url, { pushState = true } = {}) => {
        const currentMain = document.querySelector('.admin-dashboard-main');
        const currentBreadcrumb = document.querySelector('.admin-breadcrumb');
        const currentNavigation = document.querySelector('.admin-dashboard-nav');

        if (!currentMain || !currentBreadcrumb || !currentNavigation || adminNavigationInProgress) {
            window.location.assign(url.href);
            return;
        }

        adminNavigationInProgress = true;

        try {
            const response = await fetch(url.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Unable to load the selected admin page.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextMain = page.querySelector('.admin-dashboard-main');
            const nextBreadcrumb = page.querySelector('.admin-breadcrumb');
            const nextNavigation = page.querySelector('.admin-dashboard-nav');

            if (!nextMain || !nextBreadcrumb || !nextNavigation) {
                throw new Error('The selected page cannot be loaded in place.');
            }

            currentMain.innerHTML = nextMain.innerHTML;
            currentBreadcrumb.innerHTML = nextBreadcrumb.innerHTML;
            currentNavigation.innerHTML = nextNavigation.innerHTML;
            document.title = page.title;

            if (pushState) {
                window.history.pushState({}, '', url.href);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
            currentMain.classList.add('is-admin-spa-entering');
            window.setTimeout(() => currentMain.classList.remove('is-admin-spa-entering'), 190);
            document.dispatchEvent(new CustomEvent('admin:content-replaced'));
        } catch {
            currentMain.classList.remove('is-admin-spa-leaving');
            window.location.assign(url.href);
        } finally {
            adminNavigationInProgress = false;
        }
    };

    // Applicant and employer screens share one stable dashboard shell. Loading
    // only the main content makes these routes feel immediate while retaining
    // normal Laravel URLs, back/forward support, and a safe full-load fallback.
    const navigateWorkspacePage = async (url, { pushState = true } = {}) => {
        const currentMain = document.querySelector('.dashboard-main:not(.admin-dashboard-main)');

        if (!currentMain) {
            window.location.assign(url.href);
            return;
        }

        if (workspaceNavigationInProgress) return;

        workspaceNavigationInProgress = true;
        currentMain.classList.add('is-workspace-spa-leaving');

        try {
            const response = await fetch(url.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Unable to load the selected workspace page.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextMain = page.querySelector('.dashboard-main:not(.admin-dashboard-main)');

            if (!nextMain) {
                throw new Error('The selected page cannot be loaded in place.');
            }

            document.dispatchEvent(new CustomEvent('workspace:before-content-replaced'));
            currentMain.className = nextMain.className;
            currentMain.innerHTML = nextMain.innerHTML;

            if (page.title) {
                document.title = page.title;
            }

            if (pushState) {
                window.history.pushState({}, '', response.url || url.href);
            }

            currentMain.classList.add('is-workspace-spa-entering');
            window.setTimeout(() => currentMain.classList.remove('is-workspace-spa-entering'), 190);

            if (url.hash) {
                const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
                target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            document.dispatchEvent(new CustomEvent('workspace:content-replaced'));
        } catch {
            currentMain.classList.remove('is-workspace-spa-leaving');
            window.location.assign(url.href);
        } finally {
            workspaceNavigationInProgress = false;
        }
    };
    
    const markPageReady = () => {
        window.requestAnimationFrame(() => {
            document.body.classList.remove('page-entering', 'is-navigating', 'admin-route-leaving');
            document.body.classList.toggle('page-is-ready', hasInitialEntryAnimation);
            pageLoader?.classList.remove('is-visible');
            pageLoader?.setAttribute('aria-hidden', 'true');
        });
    };
    
    const showPageLoader = () => {
        document.body.classList.remove('page-entering', 'page-is-ready');
        document.body.classList.add('is-navigating');
        window.requestAnimationFrame(() => {
            pageLoader?.classList.add('is-visible');
            pageLoader?.setAttribute('aria-hidden', 'false');
        });
    };
    
    window.addEventListener('pageshow', markPageReady);
    
    if (pageLoader) {
        document.addEventListener('submit', (event) => {
            if (
                !event.defaultPrevented &&
                event.target instanceof HTMLFormElement &&
                event.target.method.toLowerCase() !== 'get' &&
                !isAuthPath(window.location.pathname)
            ) {
                showPageLoader();
            }
        });
    
        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || ! (event.target instanceof Element)) {
                return;
            }
    
            const link = event.target.closest('a[href]');
    
            if (
                !link ||
                link.target ||
                link.hasAttribute('download') ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey
            ) {
                return;
            }
    
            const rawHref = link.getAttribute('href')?.trim() ?? '';
    
            if (!rawHref || rawHref === '#') {
                return;
            }
    
            const url = new URL(link.href, window.location.href);
            const isSameOrigin = url.origin === window.location.origin;
            const isSamePageAnchor =
                url.pathname === window.location.pathname &&
                url.search === window.location.search &&
                (rawHref.startsWith('#') || Boolean(url.hash));
    
            if (
                isSameOrigin &&
                !isSamePageAnchor &&
                !isAuthPath(window.location.pathname) &&
                !isAuthPath(url.pathname)
            ) {
                event.preventDefault();

                if (isAdminPath(window.location.pathname) && isAdminPath(url.pathname)) {
                    navigateAdminPage(url);
                    return;
                }

                // The messages screen already swaps conversations in place.
                // Let that specialized controller handle links within messages.
                if (
                    isWorkspacePath(window.location.pathname) &&
                    isWorkspacePath(url.pathname) &&
                    !(normalizePath(window.location.pathname) === '/messages' && normalizePath(url.pathname) === '/messages')
                ) {
                    navigateWorkspacePage(url);
                    return;
                }

                showPageLoader();
                window.location.assign(url.href);
            }
        });
    }

    window.addEventListener('popstate', () => {
        if (isAdminPath(window.location.pathname)) {
            navigateAdminPage(new URL(window.location.href), { pushState: false });
        } else if (isWorkspacePath(window.location.pathname)) {
            navigateWorkspacePage(new URL(window.location.href), { pushState: false });
        }
    });

    window.addEventListener('workspace:navigate', (event) => {
        const destination = event.detail?.url;

        if (!destination) return;

        const url = new URL(destination, window.location.href);

        if (url.origin === window.location.origin && isWorkspacePath(url.pathname)) {
            navigateWorkspacePage(url, { pushState: !event.detail?.replace });
            return;
        }

        window.location.assign(url.href);
    });
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markPageReady, { once: true });
    } else {
        markPageReady();
    }
    
    if (isAuthPath(window.location.pathname)) {
        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || ! (event.target instanceof Element)) {
                return;
            }
    
            const link = event.target.closest('a[href]');
    
            if (
                !link ||
                link.target ||
                link.hasAttribute('download') ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey
            ) {
                return;
            }
    
            const url = new URL(link.href, window.location.href);
            const targetPath = normalizePath(url.pathname);
    
            if (
                url.origin !== window.location.origin ||
                !isAuthPath(targetPath) ||
                targetPath === normalizePath(window.location.pathname)
            ) {
                return;
            }
    
            event.preventDefault();
            document.body.classList.add('auth-route-leaving');
            window.location.assign(url.href);
        });
    }
    
};
