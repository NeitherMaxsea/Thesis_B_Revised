import Swal from 'sweetalert2';

// ADMIN ONLY: applicant search, filters, and approve/reject confirmation workflow.
export const initAdminReview = () => {
    const adminReview = document.querySelector('[data-admin-review]');
    
    if (adminReview) {
        const filterButtons = adminReview.querySelectorAll('[data-admin-filter]');
        const searchInput = adminReview.querySelector('[data-admin-search]');
        const reviewCards = adminReview.querySelectorAll('[data-admin-review-card]');
        const resultCount = adminReview.querySelector('[data-admin-result-count]');
        const filterEmptyState = adminReview.querySelector('[data-admin-filter-empty]');
        const normalizeReviewValue = (value = '') => value.trim().toLocaleLowerCase();
        let activeFilter = normalizeReviewValue(adminReview.dataset.defaultFilter || 'all');
    
        const applyAdminReviewFilters = () => {
            const searchTerm = normalizeReviewValue(searchInput?.value || '');
            let visibleCount = 0;
    
            reviewCards.forEach((card) => {
                const status = normalizeReviewValue(card.dataset.status || '');
                const searchableText = normalizeReviewValue(card.dataset.search || '');
                const matchesStatus = activeFilter === 'all' || status === activeFilter;
                const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
                const isVisible = matchesStatus && matchesSearch;
    
                card.classList.toggle('hidden', !isVisible);
    
                if (isVisible) {
                    visibleCount += 1;
                }
            });
    
            filterButtons.forEach((button) => {
                const isActive = normalizeReviewValue(button.dataset.adminFilter || 'all') === activeFilter;
    
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });
    
            if (resultCount) {
                resultCount.textContent = `Showing ${visibleCount} ${visibleCount === 1 ? 'applicant' : 'applicants'}`;
            }
    
            if (filterEmptyState) {
                filterEmptyState.hidden = visibleCount !== 0;
                filterEmptyState.classList.toggle('hidden', visibleCount !== 0);
            }
        };
    
        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = normalizeReviewValue(button.dataset.adminFilter || 'all');
                applyAdminReviewFilters();
            });
        });
    
        searchInput?.addEventListener('input', applyAdminReviewFilters);
    
        adminReview.querySelectorAll('[data-admin-cancel-reject]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
    
                const rejectPanel = button.closest('details');
    
                if (rejectPanel) {
                    rejectPanel.open = false;
                    rejectPanel.querySelector('summary')?.focus();
                }
            });
        });
    
        adminReview.querySelectorAll('[data-admin-decision-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (form.dataset.adminConfirmed === 'true') {
                    return;
                }
    
                event.preventDefault();
    
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
    
                if (form.dataset.adminConfirming === 'true') {
                    return;
                }
    
                form.dataset.adminConfirming = 'true';
    
                const isApproval = form.dataset.adminDecision === 'approve';
                const applicantName = form.dataset.applicantName?.trim() || 'this applicant';
                const confirmation = await Swal.fire({
                    icon: isApproval ? 'question' : 'warning',
                    title: isApproval ? `Approve ${applicantName}?` : `Reject ${applicantName}?`,
                    text: isApproval
                        ? `${applicantName}'s account access will be enabled after approval.`
                        : `${applicantName}'s account access will stay blocked after rejection.`,
                    showCancelButton: true,
                    confirmButtonText: isApproval ? 'Approve account' : 'Reject account',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: isApproval ? '#15803d' : '#dc2626',
                    reverseButtons: true,
                });
    
                delete form.dataset.adminConfirming;
    
                if (!confirmation.isConfirmed) {
                    return;
                }
    
                const submitButton = event.submitter instanceof HTMLButtonElement || event.submitter instanceof HTMLInputElement
                    ? event.submitter
                    : form.querySelector('button[type="submit"], input[type="submit"]');
    
                form.dataset.adminConfirmed = 'true';
                form.setAttribute('aria-busy', 'true');
    
                if (submitButton) {
                    submitButton.disabled = true;
    
                    if (submitButton instanceof HTMLInputElement) {
                        submitButton.value = 'Saving...';
                    } else {
                        submitButton.textContent = 'Saving...';
                    }
                }
    
                form.requestSubmit();
            });
        });
    
        applyAdminReviewFilters();
    }
    
};
