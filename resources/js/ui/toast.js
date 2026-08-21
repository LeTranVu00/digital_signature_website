export function registerToast(Alpine) {
    Alpine.data('uiToast', (duration = 5200) => ({
        show: true,
        remaining: Number(duration),
        startedAt: null,
        timer: null,

        start() {
            window.clearTimeout(this.timer);
            this.startedAt = Date.now();
            this.timer = window.setTimeout(() => this.close(), this.remaining);
        },

        close() {
            this.show = false;
            window.clearTimeout(this.timer);
        },

        pause() {
            if (! this.timer || ! this.startedAt) {
                return;
            }

            window.clearTimeout(this.timer);
            this.timer = null;
            this.remaining = Math.max(0, this.remaining - (Date.now() - this.startedAt));

            if (this.$refs.progress) {
                this.$refs.progress.style.animationPlayState = 'paused';
            }
        },

        resume() {
            if (! this.show || this.remaining <= 0) {
                return;
            }

            if (this.$refs.progress) {
                this.$refs.progress.style.animationPlayState = 'running';
            }

            this.start();
        },
    }));
}
