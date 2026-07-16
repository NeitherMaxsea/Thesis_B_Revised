// Public home-page interactions only: hero slides, scroll reveals, and FAQs.
export const initLandingPage = () => {
    const heroSlides = document.querySelectorAll('[data-hero-slide]');
    const heroCopies = document.querySelectorAll('[data-hero-copy]');
    
    if (heroSlides.length > 1) {
        const silverLine = document.querySelector('[data-hero-slide-line]');
        let currentSlide = 0;
    
        const showSlide = (nextSlide) => {
            heroSlides[currentSlide].classList.remove('is-active');
            heroSlides[nextSlide].classList.add('is-active');
    
            if (heroCopies.length > 1) {
                const currentCopy = currentSlide % heroCopies.length;
                const nextCopy = nextSlide % heroCopies.length;
    
                heroCopies[currentCopy]?.classList.remove('is-active');
                heroCopies[currentCopy]?.setAttribute('aria-hidden', 'true');
                heroCopies[nextCopy]?.classList.add('is-active');
                heroCopies[nextCopy]?.setAttribute('aria-hidden', 'false');
            }
    
            currentSlide = nextSlide;
    
            if (silverLine) {
                silverLine.classList.remove('is-active');
                void silverLine.offsetWidth;
                silverLine.classList.add('is-active');
            }
        };
    
        window.setInterval(() => {
            showSlide((currentSlide + 1) % heroSlides.length);
        }, 5500);
    }
    
    const revealItems = document.querySelectorAll('[data-scroll-reveal]');
    
    if (revealItems.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.18 });
    
        revealItems.forEach((item) => observer.observe(item));
    }
    
    const faqToggles = document.querySelectorAll('[data-faq-toggle]');
    
    if (faqToggles.length) {
        const closeFaqItem = (item, button, panel) => {
            button.setAttribute('aria-expanded', 'false');
            panel.style.maxHeight = `${panel.scrollHeight}px`;
    
            window.requestAnimationFrame(() => {
                item.classList.remove('is-open');
                panel.style.maxHeight = '0px';
            });
    
            const hideAfterClose = (event) => {
                if (event.propertyName !== 'max-height' || item.classList.contains('is-open')) {
                    return;
                }
    
                panel.hidden = true;
                panel.removeEventListener('transitionend', hideAfterClose);
            };
    
            panel.addEventListener('transitionend', hideAfterClose);
        };
    
        const openFaqItem = (item, button, panel) => {
            panel.hidden = false;
            button.setAttribute('aria-expanded', 'true');
            panel.style.maxHeight = '0px';
    
            window.requestAnimationFrame(() => {
                item.classList.add('is-open');
                panel.style.maxHeight = `${panel.scrollHeight}px`;
            });
        };
    
        const syncOpenFaqHeights = () => {
            faqToggles.forEach((button) => {
                const item = button.closest('.faq-item');
                const panel = document.getElementById(button.getAttribute('aria-controls'));
    
                if (item?.classList.contains('is-open') && panel) {
                    panel.style.maxHeight = `${panel.scrollHeight}px`;
                }
            });
        };
    
        faqToggles.forEach((button) => {
            const item = button.closest('.faq-item');
            const panel = document.getElementById(button.getAttribute('aria-controls'));
    
            if (!item || !panel) {
                return;
            }
    
            panel.style.maxHeight = '0px';
    
            button.addEventListener('click', () => {
                const isOpen = item.classList.contains('is-open');
    
                if (isOpen) {
                    closeFaqItem(item, button, panel);
                    return;
                }
    
                faqToggles.forEach((otherButton) => {
                    if (otherButton === button) {
                        return;
                    }
    
                    const otherItem = otherButton.closest('.faq-item');
                    const otherPanel = document.getElementById(otherButton.getAttribute('aria-controls'));
    
                    if (otherItem?.classList.contains('is-open') && otherPanel) {
                        closeFaqItem(otherItem, otherButton, otherPanel);
                    }
                });
    
                openFaqItem(item, button, panel);
            });
        });
    
        window.addEventListener('resize', syncOpenFaqHeights);
    }

    const jobFilterForm = document.querySelector('[data-landing-job-filters]');

    if (jobFilterForm) {
        const searchInput = jobFilterForm.querySelector('[data-landing-job-search]');
        const typeSelect = jobFilterForm.querySelector('[data-landing-job-type]');
        const locationSelect = jobFilterForm.querySelector('[data-landing-job-location]');
        const jobCards = document.querySelectorAll('[data-landing-job-card]');
        const jobPills = document.querySelectorAll('[data-landing-job-pill]');
        const noResults = document.querySelector('[data-landing-job-no-results]');
        const jobWorkspace = document.querySelector('[data-landing-job-workspace]');
        const resultCount = document.querySelector('[data-landing-job-count]');
        const resultSummary = document.querySelector('[data-landing-job-result-summary]');
        const jobDetail = document.querySelector('[data-landing-job-detail]');

        const updateJobDetail = (card, updateHistory = false) => {
            if (!jobDetail) {
                return;
            }

            const detail = (selector) => jobDetail.querySelector(selector);
            const title = card.dataset.jobTitle ?? '';
            const vacancies = Number(card.dataset.jobVacancies ?? 0);
            const salary = card.dataset.jobSalary ?? '';
            const accommodations = card.dataset.jobAccommodations ?? '';
            let requirements = [];

            try {
                requirements = JSON.parse(card.dataset.jobRequirements ?? '[]');
            } catch {
                requirements = [];
            }

            detail('[data-landing-job-detail-title]').textContent = title;
            detail('[data-landing-job-detail-employer]').textContent = card.dataset.jobEmployer ?? '';
            detail('[data-landing-job-detail-location]').textContent = card.dataset.jobLocation ?? '';
            detail('[data-landing-job-detail-salary]').textContent = salary;
            detail('[data-landing-job-detail-salary]').hidden = !salary;
            detail('[data-landing-job-detail-apply]').href = card.dataset.jobApplyUrl ?? '#';
            detail('[data-landing-job-detail-apply]').textContent = card.dataset.jobApplyLabel ?? 'Apply now';
            detail('[data-landing-job-detail-vacancies]').textContent = `${vacancies} ${vacancies === 1 ? 'vacancy' : 'vacancies'}`;
            detail('[data-landing-job-detail-type]').textContent = (card.dataset.jobType ?? '').replace('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
            detail('[data-landing-job-detail-fact-location]').textContent = card.dataset.jobLocation ?? '';
            detail('[data-landing-job-detail-description]').textContent = card.dataset.jobDescription ?? '';
            detail('[data-landing-job-detail-accommodations]').textContent = accommodations;
            detail('[data-landing-job-detail-support]').hidden = !accommodations;

            const profileImage = detail('[data-landing-job-detail-profile-image]');
            const profileFallback = detail('[data-landing-job-detail-profile-fallback]');
            const employerPhoto = card.dataset.jobEmployerPhoto ?? '';
            profileImage.src = employerPhoto;
            profileImage.hidden = !employerPhoto;
            profileFallback.textContent = (card.dataset.jobEmployer ?? '?').trim().charAt(0).toUpperCase() || '?';
            profileFallback.hidden = Boolean(employerPhoto);

            const requirementsSection = detail('[data-landing-job-detail-requirements-section]');
            const requirementsList = detail('[data-landing-job-detail-requirements]');
            requirementsList.replaceChildren(...requirements.map((requirement) => {
                const item = document.createElement('li');
                item.textContent = requirement;
                return item;
            }));
            requirementsSection.hidden = requirements.length === 0;

            jobCards.forEach((otherCard) => {
                const isSelected = otherCard === card;
                otherCard.classList.toggle('is-selected', isSelected);
                otherCard.toggleAttribute('aria-current', isSelected);
            });

            if (updateHistory) {
                window.history.replaceState({}, '', card.href);
            }
        };

        const filterJobs = () => {
            const searchTerm = searchInput?.value.trim().toLowerCase() ?? '';
            const selectedType = typeSelect?.value ?? '';
            const selectedLocation = locationSelect?.value ?? '';
            let visibleCount = 0;

            jobCards.forEach((card) => {
                const matchesSearch = !searchTerm || card.dataset.searchText.includes(searchTerm);
                const matchesType = !selectedType || card.dataset.jobType === selectedType;
                const matchesLocation = !selectedLocation || card.dataset.jobLocation === selectedLocation;
                const isVisible = matchesSearch && matchesType && matchesLocation;

                card.hidden = !isVisible;
                visibleCount += Number(isVisible);
            });

            const selectedCard = document.querySelector('[data-landing-job-card].is-selected');
            const firstVisibleCard = Array.from(jobCards).find((card) => !card.hidden);

            if (firstVisibleCard && (selectedCard?.hidden || !selectedCard)) {
                updateJobDetail(firstVisibleCard);
            }

            if (noResults) {
                noResults.hidden = visibleCount > 0 || jobCards.length === 0;
            }

            if (jobWorkspace) {
                jobWorkspace.hidden = visibleCount === 0 && jobCards.length > 0;
            }

            if (resultCount) {
                resultCount.textContent = String(visibleCount);
            }

            if (resultSummary) {
                resultSummary.textContent = `${visibleCount} ${visibleCount === 1 ? 'job' : 'jobs'} shown`;
            }
        };

        jobFilterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            filterJobs();
        });
        searchInput?.addEventListener('input', filterJobs);
        typeSelect?.addEventListener('change', filterJobs);
        locationSelect?.addEventListener('change', filterJobs);

        jobPills.forEach((pill) => {
            pill.addEventListener('click', () => {
                if (typeSelect) {
                    typeSelect.value = pill.dataset.jobType ?? '';
                }

                jobPills.forEach((otherPill) => otherPill.classList.toggle('is-active', otherPill === pill));
                filterJobs();
            });
        });

        jobCards.forEach((card) => {
            card.addEventListener('click', (event) => {
                event.preventDefault();
                updateJobDetail(card, true);
            });
        });
    }
    
};
