/**
 * Groups Manager Component
 * Handles group listing, filtering, and CRUD operations
 */
export default (initialGroups = []) => ({
    groups: initialGroups,
    searchQuery: '',

    /**
     * Initialize component
     */
    init() {
        // Initialize date picker for group date field
        this.$nextTick(() => {
            const datePicker = this.$refs.groupDatePicker;
            if (datePicker) {
                flatpickr(datePicker, {
                    dateFormat: 'd-m-Y',
                    allowInput: true
                });
            }
        });

        // Watch search query and refresh icons when results change
        this.$watch('searchQuery', () => {
            this.$nextTick(() => {
                window.refreshIcons?.();
            });
        });

        // Listen for group updated event
        window.addEventListener('group:updated', (event) => {
            this.updateGroup(event.detail);
        });

        // Listen for group deleted event
        window.addEventListener('group:deleted', (event) => {
            this.removeGroup(event.detail.id);
        });
    },

    /**
     * Computed: filtered groups based on search
     */
    get filteredGroups() {
        if (!this.searchQuery) return this.groups;
        const query = this.searchQuery.toLowerCase();
        return this.groups.filter(g =>
            g.name.toLowerCase().includes(query)
        );
    },

    /**
     * Add a new group
     */
    async addGroup(event) {
        event.preventDefault();

        const store = Alpine.store('app');
        store.startLoading();

        try {
            const form = this.$refs.addGroupForm;

            // Manually read form field values and send as JSON
            // This avoids FormData issues with Playwright/automated testing
            const nameInput = form.querySelector('input[name="name"]');
            const groupdateInput = form.querySelector('input[name="groupdate"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                name: nameInput?.value || '',
                groupdate: groupdateInput?.value || '',
                _token: tokenInput?.value || ''
            };

            const response = await http.post(form.action, data);

            if (response.data.success) {
                // Add new group to list
                this.groups.unshift({
                    id: response.data.id,
                    name: response.data.name,
                    date: response.data.date
                });

                // Clear form
                this.$refs.addGroupForm.reset();

                // Refresh icons and focus after DOM update
                this.$nextTick(() => {
                    window.refreshIcons?.();
                    this.$refs.searchInput?.focus();
                });

                Alpine.store('notifications').success(
                    response.data.message || 'Group added successfully'
                );
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error adding group'
            );
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Load group order modal
     */
    async loadOrderModal(groupId) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            const response = await http.get(`/group/${groupId}`);

            // Update modal data via store
            Alpine.store('modals').setGroupOrderData(response.data);

            // Wait for next tick to ensure data is rendered
            await this.$nextTick();

            // Open the modal using Bootstrap
            window.openModal('member-order');

            // Initialize icons
            window.refreshIcons?.();
        } catch (error) {
            Alpine.store('notifications').error('Error loading group details');
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Load group edit modal
     */
    async loadEditModal(groupId) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            const response = await http.get(`/group/${groupId}/edit`);

            // Update modal data via store
            Alpine.store('modals').setGroupEditData(response.data);

            // Wait for next tick to ensure data is rendered
            await this.$nextTick();

            // Open the modal using Bootstrap
            window.openModal('group-edit');

            // Refresh icons in modal
            window.refreshIcons?.();
        } catch (error) {
            Alpine.store('notifications').error('Error loading group details');
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Update group in list after edit
     */
    updateGroup(updatedGroup) {
        const index = this.groups.findIndex(g => g.id === updatedGroup.id);
        if (index !== -1) {
            this.groups[index] = { ...this.groups[index], ...updatedGroup };

            // Refresh icons after updating DOM
            this.$nextTick(() => {
                window.refreshIcons?.();
            });
        }
    },

    /**
     * Remove group from list after delete (optimistic update)
     */
    removeGroup(groupId) {
        this.groups = this.groups.filter(g => g.id !== groupId);

        // Refresh icons after updating DOM
        this.$nextTick(() => {
            window.refreshIcons?.();
        });
    }
});
