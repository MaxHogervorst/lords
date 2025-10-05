/**
 * Product Edit Modal Component
 * Handles product update and delete operations
 */
export default () => ({
    isLoading: false,

    /**
     * Computed property to get product data from store
     */
    get product() {
        return Alpine.store('modals').editModal.entity || {
            id: null,
            name: '',
            price: ''
        };
    },

    /**
     * Update product
     */
    async updateProduct() {
        this.isLoading = true;
        try {
            const form = document.getElementById('product-edit-form');
            const nameInput = form.querySelector('input[name="productName"]');
            const priceInput = form.querySelector('input[name="productPrice"]');

            const data = {
                productName: nameInput?.value || '',
                productPrice: priceInput?.value || ''
            };

            const response = await http.put(`/product/${this.product.id}`, data);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Product updated successfully'
                );

                // Close modal and refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error updating product'
            );
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Delete product
     */
    async deleteProduct() {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }

        this.isLoading = true;
        try {
            const response = await http.delete(`/product/${this.product.id}`);

            if (response.data.success) {
                Alpine.store('notifications').success(
                    response.data.message || 'Product deleted successfully'
                );

                // Close modal and refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error deleting product'
            );
        } finally {
            this.isLoading = false;
        }
    }
});
