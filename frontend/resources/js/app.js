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
import { initApplicantChat } from './modules/applicant-chat';
import { initApplicantProfile } from './modules/applicant-profile';
import { initEmployerDashboard } from './modules/employer-dashboard';
import { initJobMatch } from './modules/job-match';
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
initApplicantChat();
initApplicantProfile();
initEmployerDashboard();
initJobMatch();
