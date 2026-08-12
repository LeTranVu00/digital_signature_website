export function initScrollReveal() {
    const revealItems = document.querySelectorAll('[data-reveal]');

    if (!revealItems.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    document.documentElement.classList.add('reveal-enabled');

    revealItems.forEach((item) => {
        const delay = Number.parseInt(item.dataset.revealDelay ?? '0', 10);

        if (Number.isFinite(delay) && delay > 0) {
            item.style.setProperty('--reveal-delay', `${Math.min(delay, 1200)}ms`);
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.12,
    });

    revealItems.forEach((item) => observer.observe(item));
}
