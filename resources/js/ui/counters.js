export function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');

    if (!counters.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    counters.forEach((counter) => {
        counter.textContent = formatCounter(counter, Number(counter.dataset.counterTarget ?? 0));
    });

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            animateCounter(entry.target);
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.3,
    });

    counters.forEach((counter) => observer.observe(counter));
}

function animateCounter(counter) {
    const target = Number(counter.dataset.counterTarget ?? 0);
    const duration = Number(counter.dataset.counterDuration ?? 1100);
    const startedAt = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - startedAt) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(target * eased);

        counter.textContent = formatCounter(counter, current);

        if (progress < 1) {
            window.requestAnimationFrame(tick);
        }
    };

    counter.textContent = formatCounter(counter, 0);
    window.requestAnimationFrame(tick);
}

function formatCounter(counter, value) {
    const prefix = counter.dataset.counterPrefix ?? '';
    const suffix = counter.dataset.counterSuffix ?? '';
    const formatted = new Intl.NumberFormat('vi-VN').format(value);

    return `${prefix}${formatted}${suffix}`;
}
