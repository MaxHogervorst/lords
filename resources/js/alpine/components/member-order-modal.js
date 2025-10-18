/**
 * Member Order Modal Component
 * Handles order creation for a specific member
 */
export default {
    isLoading: false,

    /**
     * Save order for member
     */
    async saveOrder(event) {
        event?.preventDefault();
        this.isLoading = true;

        try {
            const form = document.getElementById('order-form');

            // Manually read form field values and send as JSON
            const amountInput = form.querySelector('input[name="amount"]');
            const productSelect = form.querySelector('select[name="product"]');
            const memberIdInput = form.querySelector('input[name="memberId"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                amount: amountInput?.value || '1',
                product: productSelect?.value || '',
                memberId: memberIdInput?.value || '',
                _token: tokenInput?.value || ''
            };

            const response = await http.post(form.action || window.location.pathname, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Order saved successfully'
                );

                // Add new order to the orders list
                const newOrder = {
                    id: Date.now(), // Temporary ID
                    created_at: response.data.date,
                    product_name: response.data.product,
                    amount: response.data.amount
                };
                Alpine.store('modals').orderModal.orders.unshift(newOrder);

                // Update order totals
                const existingTotal = Alpine.store('modals').orderModal.orderTotals.find(
                    t => t.product_id === response.data.product_id
                );
                if (existingTotal) {
                    existingTotal.count += parseInt(response.data.amount);
                } else {
                    Alpine.store('modals').orderModal.orderTotals.push({
                        product_id: response.data.product_id,
                        product_name: response.data.product,
                        count: parseInt(response.data.amount)
                    });
                }

                // Clear the form
                form.reset();

                // Don't close modal - allow adding multiple orders
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error saving order'
            );
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Initialize when modal content is loaded
     */
    init() {
        this.$nextTick(() => {
            // Refresh Lucide icons
            window.refreshIcons?.();
        });
    }
};
