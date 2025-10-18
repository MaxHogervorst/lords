/**
 * Modal Constants
 * Centralized constants for modal IDs used throughout the application
 */

export const MODALS = {
    // Edit modals
    MEMBER_EDIT: 'member-edit',
    PRODUCT_EDIT: 'product-edit',
    GROUP_EDIT: 'group-edit',

    // Order modals (member and group share the same modal)
    MEMBER_ORDER: 'member-order',
    GROUP_ORDER: 'member-order', // Same modal, different content

    // Utility modals
    CONFIRM: 'confirm-modal',
};

/**
 * Check if a modal ID is valid
 * @param {string} modalId - The modal ID to validate
 * @returns {boolean} - True if valid modal ID
 */
export function isValidModal(modalId) {
    return Object.values(MODALS).includes(modalId);
}
