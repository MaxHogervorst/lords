/**
 * Products Manager Component
 * Handles product listing, filtering, and CRUD operations
 */
export default (initialProducts = []) => ({
    products: initialProducts,
    searchQuery: '',
    editingProduct: null,

    /**
     * Initialize component
     */
    init() {
        // Component initialization
    },

    /**
     * Computed: filtered products based on search
     */
    get filteredProducts() {
        if (!this.searchQuery) return this.products;
        const query = this.searchQuery.toLowerCase();
        return this.products.filter(p =>
            p.name.toLowerCase().includes(query)
        );
    },

    /**
     * Add a new product
     */
    async addProduct(event) {
        event.preventDefault();

        const store = Alpine.store('app');
        store.startLoading();

        try {
            const form = this.$refs.addProductForm;

            // Manually read form field values and send as JSON
            const nameInput = form.querySelector('input[name="name"]');
            const priceInput = form.querySelector('input[name="productPrice"]');
            const tokenInput = form.querySelector('input[name="_token"]');

            const data = {
                name: nameInput?.value || '',
                productPrice: priceInput?.value || '',
                _token: tokenInput?.value || ''
            };

            const response = await http.post(form.action, data);

            if (response.data.success) {
                // Add new product to list
                this.products.unshift({
                    id: response.data.id,
                    name: response.data.name,
                    price: response.data.price
                });

                // Clear form
                this.$refs.addProductForm.reset();

                // Refresh icons and focus after DOM update
                this.$nextTick(() => {
                    window.refreshIcons?.();
                    this.$refs.searchInput?.focus();
                });

                Alpine.store('notifications').success(
                    response.data.message || 'Product added successfully'
                );
            }
        } catch (error) {
            Alpine.store('notifications').error(
                error.response?.data?.message || 'Error adding product'
            );
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Load edit modal content
     */
    async loadEditModal(productId) {
        const store = Alpine.store('app');
        store.startLoading();

        try {
            const response = await http.get(`/product/${productId}/edit`);

            // Update modal data via store
            Alpine.store('modals').setProductEditData(response.data);

            // Wait for next tick to ensure data is rendered
            await this.$nextTick();

            // Open the modal using Bootstrap
            window.openModal('product-edit');

            // Refresh icons in modal
            window.refreshIcons?.();
        } catch (error) {
            Alpine.store('notifications').error('Error loading product details');
        } finally {
            store.stopLoading();
        }
    },

    /**
     * Open edit modal
     */
    openEditModal(productId) {
        this.loadEditModal(productId);
    },

    /**
     * Update product in list after edit
     */
    updateProduct(updatedProduct) {
        const index = this.products.findIndex(p => p.id === updatedProduct.id);
        if (index !== -1) {
            this.products[index] = { ...this.products[index], ...updatedProduct };
        }
    },

    /**
     * Remove product from list after delete
     */
    removeProduct(productId) {
        this.products = this.products.filter(p => p.id !== productId);
    },

    /**
     * Validate number input (for price fields)
     */
    validateNumber(event, allowDecimal = true) {
        const charCode = event.which || event.keyCode;
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].includes(charCode)) {
            return true;
        }
        // Allow: minus sign
        if (charCode === 45) {
            return true;
        }
        // Allow: decimal point (only if allowDecimal and not already present)
        if (charCode === 46 && allowDecimal && event.target.value.indexOf('.') === -1) {
            return true;
        }
        // Ensure it's a number
        if (charCode < 48 || charCode > 57) {
            event.preventDefault();
            return false;
        }
        return true;
    }
});
