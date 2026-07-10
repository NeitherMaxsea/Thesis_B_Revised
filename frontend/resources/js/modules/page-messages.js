// Handles redirects after verification and server-provided toast messages.
export const initPageMessages = ({ showSweetToast, showSweetModal }) => {
    const approvedReviewRedirect = document.querySelector('[data-review-approved-redirect]');
    
    if (approvedReviewRedirect) {
        window.setTimeout(() => {
            window.location.assign(approvedReviewRedirect.dataset.reviewApprovedRedirect);
        }, 1800);
    }
    
    const emailVerifiedRedirect = document.querySelector('[data-email-verified-redirect]');
    
    if (emailVerifiedRedirect) {
        window.setTimeout(() => {
            window.location.assign(emailVerifiedRedirect.dataset.emailVerifiedRedirect);
        }, 1800);
    }

    const verificationModal = document.querySelector('[data-verification-swal]');

    if (verificationModal) {
        window.setTimeout(() => {
            showSweetModal({
                title: verificationModal.dataset.swalTitle,
                text: verificationModal.dataset.swalText,
                icon: verificationModal.dataset.swalIcon || 'success',
                confirmButtonText: 'I understand',
            });
        }, 160);
    }
    
    document.querySelectorAll('[data-swal-toast]').forEach((toast) => {
        showSweetToast({
            title: toast.dataset.swalTitle,
            text: toast.dataset.swalText,
            icon: toast.dataset.swalIcon || 'info',
        });
    });
    
};
