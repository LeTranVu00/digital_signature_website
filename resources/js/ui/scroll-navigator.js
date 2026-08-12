export function registerScrollNavigator(Alpine) {
    Alpine.data('scrollNavigator', () => ({
        visible: false,
        open: false,
        sections: [],
        activeId: null,
        ticking: false,
        reduceMotion: false,

        init() {
            this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.collectSections();
            this.update();

            window.addEventListener('scroll', () => this.requestUpdate(), { passive: true });
            window.addEventListener('resize', () => this.requestUpdate(), { passive: true });
        },

        collectSections() {
            this.sections = Array.from(document.querySelectorAll('[data-scroll-section]'))
                .map((element, index) => {
                    if (!element.id) {
                        element.id = `page-section-${index + 1}`;
                    }

                    return {
                        id: element.id,
                        label: element.dataset.scrollSection,
                        index: String(index + 1).padStart(2, '0'),
                        element,
                    };
                })
                .filter((section) => section.label);
        },

        requestUpdate() {
            if (this.ticking) {
                return;
            }

            this.ticking = true;

            window.requestAnimationFrame(() => {
                this.update();
                this.ticking = false;
            });
        },

        update() {
            this.visible = window.scrollY > 360;

            if (!this.visible) {
                this.open = false;
            }

            const current = this.sections
                .filter((section) => section.element.getBoundingClientRect().top <= 140)
                .at(-1);

            this.activeId = current?.id ?? this.sections[0]?.id ?? null;
        },

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: this.reduceMotion ? 'auto' : 'smooth',
            });
            this.close();
        },

        scrollToSection(id) {
            document.getElementById(id)?.scrollIntoView({
                behavior: this.reduceMotion ? 'auto' : 'smooth',
                block: 'start',
            });
            this.close();
        },
    }));
}
