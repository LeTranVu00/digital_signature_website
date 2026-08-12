export function registerTheme(Alpine) {
    Alpine.data('themeToggle', () => ({
        dark: document.documentElement.classList.contains('dark'),

        init() {
            window.addEventListener('storage', (event) => {
                if (event.key === 'theme') {
                    this.dark = document.documentElement.classList.contains('dark');
                }
            });
        },

        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            window.localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
    }));
}
