export function registerSidebar(Alpine) {
    Alpine.data('adminShell', () => ({
        sidebarOpen: false,
        sidebarCollapsed: false,

        closeSidebar() {
            this.sidebarOpen = false;
        },

        openSidebar() {
            this.sidebarOpen = true;
        },

        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },
    }));

    Alpine.data('frontendHeader', () => ({
        mobileMenuOpen: false,
        scrolled: false,

        init() {
            this.updateScrolled();
        },

        updateScrolled() {
            this.scrolled = window.scrollY > 12;
        },

        closeMobileMenu() {
            this.mobileMenuOpen = false;
        },

        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
    }));
}
