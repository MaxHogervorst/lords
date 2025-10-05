/**
 * Invoice Manager Component
 * Handles invoice group selection and management
 */
export default () => ({
    showNewMonthModal: false,

    /**
     * Initialize component
     */
    init() {
        // Initialize Flatpickr for month picker if in modal
        this.$nextTick(() => {
            const monthPicker = this.$refs.invoiceMonthPicker;
            if (monthPicker) {
                flatpickr(monthPicker, {
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: true,
                            dateFormat: 'm-Y',
                            altFormat: 'F Y'
                        })
                    ]
                });
            }
        });
    },

    /**
     * Select an invoice group/month
     */
    async selectInvoiceGroup(event) {
        event.preventDefault();

        const store = Alpine.store('app');
        store.startLoading();

        try {
            const formData = new FormData(this.$refs.selectInvoiceForm);
            const response = await http.post('/invoice/selectinvoicegroup', formData);

            if (response.data.success) {
                // Store message for after refresh
                localStorage.setItem('afterRefreshMessage', JSON.stringify({
                    text: response.data.message,
                    type: 'success'
                }));

                // Reload to show new invoice group data
                window.location.reload();
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error selecting month'
            );
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Create a new invoice month/group
     */
    async createNewMonth(event) {
        event.preventDefault();

        const store = Alpine.store('app');
        store.startLoading();

        try {
            const formData = new FormData(this.$refs.newMonthForm);
            const response = await http.post('/invoice/storeinvoicegroup', formData);

            if (response.data.success) {
                // Store message for after refresh
                localStorage.setItem('afterRefreshMessage', JSON.stringify({
                    text: response.data.message || 'Invoice month created successfully',
                    type: 'success'
                }));

                // Reload to show new invoice group
                window.location.reload();
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error creating month'
            );
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Toggle new month modal
     */
    toggleNewMonthModal() {
        this.showNewMonthModal = !this.showNewMonthModal;
    }
});
