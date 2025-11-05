/**
 * Fiscus Manager Component
 * Handles fiscus product listing, filtering, and create/edit modal
 */
export default (initialProducts = [], initialMembers = []) => ({
    // List view state
    products: initialProducts,
    searchQuery: '',

    // Modal state
    isOpen: false,
    mode: 'create', // 'create' or 'edit'
    isLoading: false,

    // All members data
    members: initialMembers,

    // Form data
    form: {
        productId: null,
        productName: '',
        productDescription: '',
        productTotalPrice: '',
        productPricePerPerson: '',
        selectedMembers: [],
        memberSearchQuery: ''
    },

    /**
     * Initialize component
     */
    init() {
        // Watch search query and refresh icons when results change
        this.$watch('searchQuery', () => {
            this.$nextTick(() => {
                window.refreshIcons?.();
            });
        });

        // Refresh icons when modal opens
        this.$watch('isOpen', (value) => {
            if (value) {
                this.$nextTick(() => {
                    window.refreshIcons?.();
                });
            }
        });
    },

    /**
     * Computed: filtered products for list view
     */
    get filteredProducts() {
        if (!this.searchQuery) return this.products;
        const query = this.searchQuery.toLowerCase();
        return this.products.filter(p =>
            p.name.toLowerCase().includes(query)
        );
    },

    /**
     * Computed: filtered members for form
     */
    get filteredMembers() {
        if (!this.form.memberSearchQuery) return this.members;
        const query = this.form.memberSearchQuery.toLowerCase();
        return this.members.filter(m =>
            m.firstname.toLowerCase().includes(query) ||
            m.lastname.toLowerCase().includes(query)
        );
    },

    /**
     * Computed: selected member count
     */
    get selectedMemberCount() {
        return this.form.selectedMembers.length;
    },

    /**
     * Computed: calculated total price
     */
    get calculatedTotalPrice() {
        if (this.form.productTotalPrice) {
            return parseFloat(this.form.productTotalPrice) || 0;
        }
        if (this.form.productPricePerPerson && this.selectedMemberCount > 0) {
            return (parseFloat(this.form.productPricePerPerson) || 0) * this.selectedMemberCount;
        }
        return 0;
    },

    /**
     * Computed: calculated price per person
     */
    get calculatedPricePerPerson() {
        if (this.form.productPricePerPerson) {
            return parseFloat(this.form.productPricePerPerson) || 0;
        }
        if (this.form.productTotalPrice && this.selectedMemberCount > 0) {
            return (parseFloat(this.form.productTotalPrice) || 0) / this.selectedMemberCount;
        }
        return 0;
    },

    /**
     * Open modal in create mode
     */
    openCreate() {
        this.mode = 'create';
        this.resetForm();
        this.isOpen = true;
    },

    /**
     * Open modal in edit mode
     */
    async openEdit(productId) {
        this.mode = 'edit';
        this.isLoading = true;
        this.isOpen = true;

        try {
            // Load product prices
            const pricesResponse = await http.get(`/fiscus/invoiceprices/${productId}`);
            const prices = pricesResponse.data;

            if (prices.length > 0) {
                // Use the first price (or latest)
                const price = prices[0];

                // Load invoice lines for this price to get selected members
                const linesResponse = await http.get(`/fiscus/specificinvoicelines/${price.id}`);
                const lines = linesResponse.data;

                // Find product in our list
                const product = this.products.find(p => p.id === productId);

                // Populate form
                this.form.productId = productId;
                this.form.productName = product?.name || '';
                this.form.productDescription = price.description || '';
                this.form.productPricePerPerson = price.price || '';
                this.form.productTotalPrice = '';
                this.form.selectedMembers = lines.map(line => line.member_id);
                this.form.memberSearchQuery = '';
            }
        } catch (error) {
            Alpine.store('notifications').error('Error loading product data');
            console.error('Error loading product:', error);
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Close modal
     */
    close() {
        this.isOpen = false;
        this.resetForm();
    },

    /**
     * Reset form to initial state
     */
    resetForm() {
        this.form = {
            productId: null,
            productName: '',
            productDescription: '',
            productTotalPrice: '',
            productPricePerPerson: '',
            selectedMembers: [],
            memberSearchQuery: ''
        };
    },

    /**
     * Select all filtered members
     */
    selectAllMembers() {
        this.form.selectedMembers = this.filteredMembers.map(m => m.id);
        this.recalculatePrices();
    },

    /**
     * Deselect all members
     */
    deselectAllMembers() {
        this.form.selectedMembers = [];
        this.recalculatePrices();
    },

    /**
     * Calculate total price from per-person price
     */
    calculateTotalPrice() {
        if (this.form.productPricePerPerson && this.selectedMemberCount > 0) {
            this.form.productTotalPrice = '';
        }
    },

    /**
     * Calculate per-person price from total price
     */
    calculatePricePerPerson() {
        if (this.form.productTotalPrice && this.selectedMemberCount > 0) {
            this.form.productPricePerPerson = '';
        }
    },

    /**
     * Recalculate prices when member selection changes
     */
    recalculatePrices() {
        // If total price is set, recalculate per-person
        if (this.form.productTotalPrice && this.selectedMemberCount > 0) {
            // Keep total, per-person will auto-calculate
        }
        // If per-person is set, recalculate total
        else if (this.form.productPricePerPerson && this.selectedMemberCount > 0) {
            // Keep per-person, total will auto-calculate
        }
    },

    /**
     * Save product (create or update)
     */
    async save() {
        // Validate
        if (!this.form.productName) {
            Alpine.store('notifications').error('Product name is required');
            return;
        }

        if (!this.calculatedPricePerPerson) {
            Alpine.store('notifications').error('Price per person is required');
            return;
        }

        if (this.selectedMemberCount === 0) {
            Alpine.store('notifications').error('Select at least 1 member');
            return;
        }

        this.isLoading = true;

        try {
            const data = {
                _token: document.querySelector('meta[name="csrf-token"]')?.content,
                finalproductname: this.form.productName,
                finalproductdescription: this.form.productDescription,
                finaltotalprice: this.calculatedTotalPrice,
                finalpriceperperson: this.calculatedPricePerPerson,
                finalselectedmembers: this.selectedMemberCount,
                member: this.form.selectedMembers
            };

            let response;
            if (this.mode === 'create') {
                response = await http.post('/fiscus', data);
            } else {
                response = await http.put(`/fiscus/${this.form.productId}`, data);
            }

            if (response.data.success) {
                // Close modal immediately to show success
                this.close();

                // Show success notification
                Alpine.store('notifications').success(response.data.message);

                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            // Handle validation errors (422)
            if (error.response?.status === 422 && error.response?.data?.errors) {
                const errors = error.response.data.errors;
                Object.keys(errors).forEach(field => {
                    const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    messages.forEach(msg => {
                        Alpine.store('notifications').error(msg);
                    });
                });
            } else {
                Alpine.store('notifications').error(error.response?.data?.message || 'Error saving product');
            }
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Delete product
     */
    async deleteProduct() {
        if (!this.form.productId) {
            Alpine.store('notifications').error('No product selected');
            return;
        }

        // Confirm deletion
        if (!confirm('Are you sure you want to delete this product? This will also delete all associated prices and invoice lines.')) {
            return;
        }

        this.isLoading = true;

        try {
            const response = await http.delete(`/fiscus/${this.form.productId}`);

            if (response.data.success) {
                // Close modal immediately to show success
                this.close();

                // Show success notification
                Alpine.store('notifications').success(response.data.message);

                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            Alpine.store('notifications').error(error.response?.data?.message || 'Error deleting product');
        } finally {
            this.isLoading = false;
        }
    }
});
