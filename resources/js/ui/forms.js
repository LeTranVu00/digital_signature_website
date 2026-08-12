export function registerForms(Alpine) {
    Alpine.data('postEditorForm', () => ({
        submitting: false,
        thumbnailPreview: null,
        thumbnailPreviewUrl: null,

        markSubmitting() {
            this.submitting = true;
        },

        previewThumbnail(event) {
            const file = event.target.files[0];

            if (this.thumbnailPreviewUrl) {
                URL.revokeObjectURL(this.thumbnailPreviewUrl);
            }

            if (!file) {
                this.thumbnailPreview = null;
                this.thumbnailPreviewUrl = null;
                return;
            }

            this.thumbnailPreviewUrl = URL.createObjectURL(file);
            this.thumbnailPreview = this.thumbnailPreviewUrl;
        },
    }));
}

export function initFormScrollRestoration() {
    const key = 'ui.formScrollY';
    const savedScroll = window.sessionStorage.getItem(key);

    if (savedScroll && !window.location.hash) {
        window.sessionStorage.removeItem(key);
        window.requestAnimationFrame(() => {
            window.scrollTo({
                top: Number(savedScroll),
                behavior: 'auto',
            });
        });
    }

    document.addEventListener('submit', (event) => {
        if (!event.target.matches('[data-preserve-scroll]')) {
            return;
        }

        window.sessionStorage.setItem(key, String(window.scrollY));
    });
}
