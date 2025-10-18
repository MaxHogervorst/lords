<div x-data="productEditModal" x-show="$store.modals.editModal.type === 'product'">
    <div class="modal-header">
        <h5 class="modal-title" x-text="product.name"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <form id="product-edit-form" @submit.prevent="updateProduct">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input
                    type="text"
                    name="productName"
                    :value="product.name"
                    placeholder="Product name"
                    class="form-control"
                    data-testid="product-name-input"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label">Product Price</label>
                <input
                    type="text"
                    name="productPrice"
                    :value="product.price"
                    placeholder="Product price"
                    class="form-control"
                    data-testid="product-price-input"
                    required>
            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button
            type="button"
            class="btn btn-danger"
            @click="deleteProduct"
            :disabled="isLoading"
            data-testid="product-delete-button">
            <i data-lucide="trash-2"></i>
            <span x-text="isLoading ? 'Deleting...' : 'Delete Product'"></span>
        </button>
        <button type="submit" class="btn btn-primary" form="product-edit-form" :disabled="isLoading" data-testid="product-save-button">
            <i data-lucide="save"></i>
            <span x-text="isLoading ? 'Saving...' : 'Save Changes'"></span>
        </button>
    </div>
</div>