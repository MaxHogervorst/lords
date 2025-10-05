import { serializeFormWithCheckboxes, getFieldValue } from '../../utils/form-helpers.js';
import { MODALS } from '../../constants/modals.js';

/**
 * Member Edit Modal Component
 * Handles member update and delete operations
 */
export default () => ({
    isLoading: false,

    /**
     * Computed property to get member data from store
     */
    get member() {
        return Alpine.store('modals').editModal.entity || {
            id: null,
            firstname: '',
            lastname: '',
            bic: '',
            iban: '',
            had_collection: false
        };
    },

    /**
     * Update member with optimistic update
     */
    async updateMember() {
        this.isLoading = true;
        try {
            const form = document.getElementById('member-edit-form');

            // Use form helper to serialize data
            const formData = serializeFormWithCheckboxes(form);

            const data = {
                name: formData.name || '',
                lastname: formData.lastname || '',
                bic: formData.bic || '',
                iban: formData.iban || '',
                had_collection: formData.had_collection || '0',
                _token: formData._token || '',
                _method: 'PUT'
            };

            const response = await http.post(`/member/${this.member.id}`, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Member updated successfully'
                );

                // Emit event for parent component to update list
                window.dispatchEvent(new CustomEvent('member:updated', {
                    detail: {
                        id: this.member.id,
                        firstname: data.name,
                        lastname: data.lastname,
                        bic: data.bic,
                        iban: data.iban,
                        had_collection: data.had_collection === '1'
                    }
                }));

                // Close modal after save
                window.closeModal(MODALS.MEMBER_EDIT);
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error updating member'
            );
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Delete member with optimistic update
     */
    async deleteMember() {
        this.isLoading = true;
        try {
            // Show confirmation modal instead of native confirm()
            await window.confirmAction({
                title: 'Delete Member',
                message: 'Are you sure you want to delete this member? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                dangerous: true
            });
            // Get CSRF token from the form
            const form = document.getElementById('member-edit-form');
            const tokenValue = getFieldValue(form, '_token', '');

            const data = {
                _token: tokenValue,
                _method: 'DELETE'
            };

            const response = await http.post(`/member/${this.member.id}`, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Member deleted successfully'
                );

                // Emit event for parent component to remove from list
                window.dispatchEvent(new CustomEvent('member:deleted', {
                    detail: { id: this.member.id }
                }));

                // Close modal
                window.closeModal(MODALS.MEMBER_EDIT);
            }
        } catch (error) {
            // If user cancelled, don't show error
            if (error.message === 'User cancelled') {
                this.isLoading = false;
                return;
            }

            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error deleting member'
            );
            this.isLoading = false;
        }
    }
});
