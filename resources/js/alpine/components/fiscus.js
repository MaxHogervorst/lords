/**
 * Fiscus Manager Component
 * Handles fiscus product listing and filtering
 */
export default (initialProducts = []) => ({
    products: initialProducts,
    searchQuery: '',

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
    }
});
