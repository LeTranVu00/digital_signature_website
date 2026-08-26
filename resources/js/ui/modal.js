export function registerModal(Alpine) {
    Alpine.data('uiModal', ({ name, show = false, sessionKey = null }) => ({
        name,
        show,
        sessionKey,
        previousActiveElement: null,

        init() {
            if (this.sessionKey && window.sessionStorage.getItem(this.sessionKey) === 'closed') {
                this.show = false;
            }

            if (this.show) {
                this.open();
            }
        },

        focusableElements() {
            const selector = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

            return Array.from(this.$refs.panel.querySelectorAll(selector))
                .filter((element) => element.offsetParent !== null);
        },

        firstFocusable() {
            return this.focusableElements()[0];
        },

        lastFocusable() {
            return this.focusableElements().at(-1);
        },

        open() {
            this.previousActiveElement = document.activeElement;
            this.show = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => {
                (this.firstFocusable() || this.$refs.panel).focus();
            });
        },

        close() {
            this.show = false;
            if (this.sessionKey) {
                window.sessionStorage.setItem(this.sessionKey, 'closed');
            }
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => {
                if (this.previousActiveElement && typeof this.previousActiveElement.focus === 'function') {
                    this.previousActiveElement.focus();
                }
            });
        },

        openFromEvent(event) {
            if (event.detail === this.name) {
                this.open();
            }
        },

        closeFromEvent(event) {
            if (event.detail === this.name) {
                this.close();
            }
        },

        trapTab(event) {
            const first = this.firstFocusable();
            const last = this.lastFocusable();

            if (!first || !last) {
                event.preventDefault();
                return;
            }

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('legacyModal', ({ show = false, focusOnOpen = false }) => ({
        show,
        focusOnOpen,

        init() {
            if (this.show) {
                document.body.classList.add('overflow-y-hidden');

                if (this.focusOnOpen) {
                    window.setTimeout(() => this.firstFocusable()?.focus(), 100);
                }
            }

            this.$watch('show', (value) => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');

                    if (this.focusOnOpen) {
                        window.setTimeout(() => this.firstFocusable()?.focus(), 100);
                    }
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                }
            });
        },

        focusables() {
            const selector = 'a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])';

            return Array.from(this.$el.querySelectorAll(selector))
                .filter((element) => !element.hasAttribute('disabled'));
        },

        firstFocusable() {
            return this.focusables()[0];
        },

        lastFocusable() {
            return this.focusables().at(-1);
        },

        nextFocusable() {
            return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
        },

        prevFocusable() {
            return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
        },

        nextFocusableIndex() {
            return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1);
        },

        prevFocusableIndex() {
            return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1;
        },

        open() {
            this.show = true;
        },

        close() {
            this.show = false;
        },
    }));
}
