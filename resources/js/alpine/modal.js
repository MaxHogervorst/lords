/**
 * Alpine.js Modal Component using Bootstrap 5 modals
 * Works with Tabler CSS framework
 */

// Get Bootstrap from Tabler's bundle or window
function getBootstrap() {
    // Try window.bootstrap first (Tabler should expose it)
    if (typeof window.bootstrap !== 'undefined') {
        return window.bootstrap;
    }

    console.error('[Bootstrap] Bootstrap is not available on window.bootstrap');
    return null;
}

export function initModals() {
    // Register modal component for Bootstrap modal elements
    Alpine.data('modal', (modalId) => ({
        modal: null,

        init() {
            // Get modal element
            const modalEl = document.getElementById(modalId);
            const Bootstrap = getBootstrap();
            if (modalEl && Bootstrap) {
                this.modal = new Bootstrap.Modal(modalEl);
            }
        },

        open() {
            if (this.modal) {
                this.modal.show();
            }
        },

        close() {
            if (this.modal) {
                this.modal.hide();
            }
        }
    }));
}

// Helper function to open modals from anywhere
export function openModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) {
        if (typeof window.logger !== 'undefined') {
            window.logger.error('Modal element not found', { modalId });
        } else {
            console.error('[openModal] Modal element not found:', modalId);
        }
        return;
    }

    const Bootstrap = getBootstrap();
    if (!Bootstrap) {
        if (typeof window.logger !== 'undefined') {
            window.logger.error('Cannot open modal - Bootstrap not available');
        } else {
            console.error('[openModal] Cannot open modal - Bootstrap not available');
        }
        return;
    }

    let modal = Bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new Bootstrap.Modal(modalEl);
    }
    modal.show();
}

export function closeModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    const Bootstrap = getBootstrap();
    if (!Bootstrap) {
        if (typeof window.logger !== 'undefined') {
            window.logger.error('Cannot close modal - Bootstrap not available');
        } else {
            console.error('[closeModal] Cannot close modal - Bootstrap not available');
        }
        return;
    }

    const modal = Bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

// Make modal functions globally available
if (typeof window !== 'undefined') {
    window.openModal = openModal;
    window.closeModal = closeModal;
}
