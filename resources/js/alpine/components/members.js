/**
 * Members Manager Component
 * Handles member listing, filtering, and CRUD operations
 */
export default (initialMembers = []) => ({
    members: initialMembers,
    searchFirstName: '',
    searchLastName: '',
    filterBankInfo: false,
    filterCollection: false,

    /**
     * Initialize component
     */
    init() {
        // Watch for ESC key to clear filters
        this.$watch('searchFirstName', () => {
            if (this.searchFirstName === '') {
                this.$nextTick(() => this.$refs.firstNameSearch?.focus());
            }
        });

        // Listen for member updated event
        window.addEventListener('member:updated', (event) => {
            this.updateMember(event.detail);
        });

        // Listen for member deleted event
        window.addEventListener('member:deleted', (event) => {
            this.removeMember(event.detail.id);
        });
    },

    /**
     * Computed: filtered members based on search and filters
     */
    get filteredMembers() {
        let filtered = this.members;

        // Search filter - first name
        if (this.searchFirstName) {
            const query = this.searchFirstName.toLowerCase();
            filtered = filtered.filter(m =>
                m.firstname.toLowerCase().includes(query)
            );
        }

        // Search filter - last name
        if (this.searchLastName) {
            const query = this.searchLastName.toLowerCase();
            filtered = filtered.filter(m =>
                m.lastname.toLowerCase().includes(query)
            );
        }

        // Bank info filter (show only members with missing bank info)
        if (this.filterBankInfo) {
            filtered = filtered.filter(m =>
                !m.bic || !m.iban || m.bic === '' || m.iban === ''
            );
        }

        // Collection filter (show only members who haven't had collection)
        if (this.filterCollection) {
            filtered = filtered.filter(m => !m.had_collection);
        }

        return filtered;
    },

    /**
     * Handle form submission - prevent default and call addMember
     */
    async handleSubmit(event) {
        event.preventDefault();
        return this.addMember(event);
    },

    /**
     * Add a new member
     */
    async addMember(event) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            // Create FormData and manually add input values
            // This ensures values set programmatically (e.g., in tests) are captured
            const form = this.$refs.addMemberForm;

            // Manually read form field values and send as JSON
            // This avoids FormData issues with Playwright/automated testing
            const nameInput = form.querySelector('input[name="name"]');
            const lastnameInput = form.querySelector('input[name="lastname"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                name: nameInput?.value || '',
                lastname: lastnameInput?.value || '',
                _token: tokenInput?.value || ''
            };

            const response = await http.post(form.action, data);

            if (response.data.success) {
                // Add new member to list
                this.members.unshift({
                    id: response.data.id,
                    firstname: response.data.firstname,
                    lastname: response.data.lastname,
                    bic: '',
                    iban: '',
                    had_collection: false
                });

                // Clear form
                this.$refs.addMemberForm.reset();

                // Refresh icons and focus after DOM update
                this.$nextTick(() => {
                    window.refreshIcons?.();
                    this.$refs.firstNameSearch?.focus();
                });

                Alpine.store('notifications').success(
                    response.data.message || 'Member added successfully'
                );
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error adding member'
            );
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Load member order modal
     */
    async loadOrderModal(memberId) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            const response = await http.get(`/member/${memberId}`);

            // Update modal data via store
            Alpine.store('modals').setMemberOrderData(response.data);

            // Wait for next tick to ensure data is rendered
            await this.$nextTick();

            // Open the modal using Bootstrap
            window.openModal('member-order');

            // Initialize icons
            window.refreshIcons?.();
        } catch (error) {
            Alpine.store('notifications').error('Error loading member details');
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Load member edit modal
     */
    async loadEditModal(memberId) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            const response = await http.get(`/member/${memberId}/edit`);

            // Update modal data via store
            Alpine.store('modals').setMemberEditData(response.data);

            // Wait for next tick to ensure data is rendered
            await this.$nextTick();

            // Open the modal using Bootstrap
            window.openModal('member-edit');

            // Refresh icons in modal
            window.refreshIcons?.();
        } catch (error) {
            Alpine.store('notifications').error('Error loading member details');
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Clear all filters and search
     */
    clearFilters() {
        this.searchFirstName = '';
        this.searchLastName = '';
        this.filterBankInfo = false;
        this.filterCollection = false;
    },

    /**
     * Update member in list after edit (optimistic update)
     */
    updateMember(updatedMember) {
        const index = this.members.findIndex(m => m.id === updatedMember.id);
        if (index !== -1) {
            this.members[index] = { ...this.members[index], ...updatedMember };

            // Refresh icons after updating DOM
            this.$nextTick(() => {
                window.refreshIcons?.();
            });
        }
    },

    /**
     * Remove member from list after delete (optimistic update)
     */
    removeMember(memberId) {
        this.members = this.members.filter(m => m.id !== memberId);

        // Refresh icons after updating DOM
        this.$nextTick(() => {
            window.refreshIcons?.();
        });
    }
});
