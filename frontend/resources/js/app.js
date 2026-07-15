import './bootstrap';
import 'sweetalert2/dist/sweetalert2.min.css';

import { applyTranslations, getCurrentInterfaceLanguage } from './modules/translations';
import { initPageTransitions } from './modules/page-transitions';
import { initAccessibilityWidget } from './modules/accessibility';
import { initNavigation } from './modules/navigation';
import { initAdminReview } from './modules/admin-review';
import { initPageMessages } from './modules/page-messages';
import { initLandingPage } from './modules/landing-page';
import { initAuthRegistration } from './modules/auth-registration';
import { initAdminDashboard } from './modules/admin-dashboard';
import { initAdminAccountRealtime } from './modules/admin-account-realtime';
import { destroyApplicantChat, initApplicantChat } from './modules/applicant-chat';
import { destroyApplicantProfile, initApplicantProfile } from './modules/applicant-profile';
import { initEmployerDashboard } from './modules/employer-dashboard';
import { initEmployerProfile } from './modules/employer-profile';
import { destroyJobMatch, initJobMatch } from './modules/job-match';
import { initRealtimeInbox } from './modules/realtime-inbox';
import { initUserActivity } from './modules/user-activity';
import { showSweetModal, showSweetToast } from './modules/notifications';

// Application entry point: keep this file as orchestration only.
// Put role-specific JavaScript in its matching module to avoid cross-role side effects.
initPageTransitions();
initAccessibilityWidget({ applyTranslations, getCurrentInterfaceLanguage });
initNavigation();
initAdminReview();
initPageMessages({ showSweetToast, showSweetModal });
initLandingPage();
initAuthRegistration({ applyTranslations, getCurrentInterfaceLanguage, showSweetToast });
initAdminDashboard();
initAdminAccountRealtime({ showSweetToast });
initRealtimeInbox({ showSweetToast });
initUserActivity();
initApplicantChat();
initApplicantProfile();
initEmployerDashboard();
initEmployerProfile();
initJobMatch();

// Dashboard routes replace only <main>, so initialize the small controllers
// that belong to the new screen without re-registering global listeners.
document.addEventListener('workspace:before-content-replaced', () => {
    destroyApplicantChat();
    destroyApplicantProfile();
    destroyJobMatch();
});
document.addEventListener('workspace:content-replaced', () => {
    initPageMessages({ showSweetToast, showSweetModal });
    initApplicantChat();
    initApplicantProfile();
    initEmployerDashboard();
    initEmployerProfile();
    initJobMatch();
});
