import { MODALS } from '../../constants/modals.js';

/**
 * Confirmation Modal Component
 * Replaces native confirm() dialog with a Bootstrap modal
 * Allows browser tests to interact with delete confirmations
 */
export default () => ({
    isVisible: false,
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    confirmCallback: null,
    isDangerous: false,

    /**
     * Show confirmation modal
     * @param {Object} options - Configuration options
     * @param {string} options.title - Modal title
     * @param {string} options.message - Confirmation message
     * @param {string} options.confirmText - Confirm button text
     * @param {string} options.cancelText - Cancel button text
     * @param {Function} options.onConfirm - Callback when confirmed
     * @param {boolean} options.dangerous - Style as dangerous action (red button)
     */
    show(options = {}) {
        this.title = options.title || 'Confirm Action';
        this.message = options.message || 'Are you sure you want to proceed?';
        this.confirmText = options.confirmText || 'Confirm';
        this.cancelText = options.cancelText || 'Cancel';
        this.confirmCallback = options.onConfirm || null;
        this.isDangerous = options.dangerous || false;
        this.isVisible = true;

        // Open the modal
        this.$nextTick(() => {
            window.openModal(MODALS.CONFIRM);
        });
    },

    /**
     * Handle confirm button click
     */
    async handleConfirm() {
        if (this.confirmCallback) {
            await this.confirmCallback();
        }
        this.close();
    },

    /**
     * Handle cancel button click
     */
    handleCancel() {
        this.close();
    },

    /**
     * Close the modal and reset state
     */
    close() {
        window.closeModal(MODALS.CONFIRM);
        this.isVisible = false;
        this.confirmCallback = null;
    }
});
