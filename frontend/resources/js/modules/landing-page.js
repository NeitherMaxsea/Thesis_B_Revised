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
    
};
