/**
 * Group Edit Modal Component
 * Handles group update and delete operations
 */
export default () => ({
    isLoading: false,

    /**
     * Computed property to get group data from store
     */
    get group() {
        return Alpine.store('modals').editModal.entity || {
            id: null,
            name: ''
        };
    },

    /**
     * Update group
     */
    async updateGroup() {
        this.isLoading = true;
        try {
            const form = document.getElementById('group-edit-form');
            const nameInput = form.querySelector('input[name="name"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                name: nameInput?.value || '',
                _token: tokenInput?.value || '',
                _method: 'PUT'
            };

            const response = await http.post(`/group/${this.group.id}`, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Group updated successfully'
                );

                // Close modal and refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error updating group'
            );
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Delete group
     */
    async deleteGroup() {
        if (!confirm('Are you sure you want to delete this group?')) {
            return;
        }

        this.isLoading = true;
        try {
            // Get CSRF token from the form
            const form = document.getElementById('group-edit-form');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                _token: tokenInput?.value || '',
                _method: 'DELETE'
            };

            const response = await http.post(`/group/${this.group.id}`, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Group deleted successfully'
                );

                // Close modal and refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error deleting group'
            );
        } finally {
            this.isLoading = false;
        }
    }
});
