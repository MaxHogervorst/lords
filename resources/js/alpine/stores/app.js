/**
 * App Store
 * Manages global application state
 */
export default {
    // Loading state
    isLoading: false,
    loadingCount: 0,

    /**
     * Start loading (can be called multiple times)
     */
    startLoading() {
        this.loadingCount++;
        this.isLoading = true;
    },

    /**
     * Stop loading (decrements counter)
     */
    stopLoading() {
        this.loadingCount = Math.max(0, this.loadingCount - 1);
        if (this.loadingCount === 0) {
            this.isLoading = false;
        }
    },

    /**
     * Force stop all loading
     */
    resetLoading() {
        this.loadingCount = 0;
        this.isLoading = false;
    },

    // User authentication
    user: null,
    isAdmin: false,

    /**
     * Set current user
     */
    setUser(user) {
        this.user = user;
        this.isAdmin = user?.is_admin || false;
    },

    // Modal state
    activeModal: null,

    /**
     * Open a modal
     */
    openModal(modalName) {
        this.activeModal = modalName;
    },

    /**
     * Close active modal
     */
    closeModal() {
        this.activeModal = null;
    }
};
