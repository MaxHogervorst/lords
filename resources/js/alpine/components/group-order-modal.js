/**
 * Group Order Modal Component
 * Handles order creation and group member management for groups
 */
export default {
    isLoading: false,
    activeTab: 'orders',

    /**
     * Save order for group
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
     * Add member to group
     */
    async addGroupMember(event) {
        event?.preventDefault();
        this.isLoading = true;

        try {
            const form = document.getElementById('add-groupmembers-form');

            // Manually read form field values and send as JSON
            const memberSelect = form.querySelector('select[name="member"]');
            const groupidInput = form.querySelector('input[name="groupid"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                member: memberSelect?.value || '',
                groupid: groupidInput?.value || '',
                _token: tokenInput?.value || ''
            };

            const response = await http.post('/groupmembers', data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Member added to group successfully'
                );

                // Clear the form
                form.reset();

                // Close modal
                const modalEl = this.$el.closest('.modal');
                if (modalEl) {
                    window.closeModal(modalEl.id);
                }
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error adding member to group'
            );
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Delete member from group
     */
    async deleteGroupMember(pivotId) {
        if (!confirm('Are you sure you want to remove this member from the group?')) {
            return;
        }

        this.isLoading = true;

        try {
            const response = await http.delete(`/group/groupmember/${pivotId}`);

            if (response.data.success) {
                Alpine.store('notifications').success(response.data.message);

                // Remove member from store
                const store = Alpine.store('modals');
                store.orderModal.groupMembers = store.orderModal.groupMembers.filter(
                    m => m.pivot_id !== pivotId
                );
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error removing member from group'
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
