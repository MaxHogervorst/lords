@extends('layout.master')
@section('script')
<script>
    // Alpine.js component for Fiscus Edit wizard
    document.addEventListener('alpine:init', () => {
        Alpine.data('fiscusEditWizard', () => ({
            currentStep: 1,
            totalSteps: 4,
            isLoading: false,

            // Form data
            selectedProduct: '{{ request()->query("product", "") }}',
            selectedPrice: '',
            productTotalPrice: '',
            productPricePerPerson: '',
            productDescription: '',
            searchQuery: '',
            products: @json($products),
            members: @json($members),
            selectedMembers: [],
            availablePrices: [],
            pricesData: [],

            async init() {
                // If a product is pre-selected via query parameter, go to step 2 and load prices
                if (this.selectedProduct) {
                    this.currentStep = 2;
                    await this.loadPrices();
                }
            },

            async loadPrices() {
                if (!this.selectedProduct) return;

                this.isLoading = true;
                try {
                    const response = await http.get(`{{ url('fiscus/invoiceprices') }}/${this.selectedProduct}`);
                    this.pricesData = response.data;
                    this.availablePrices = response.data.map(p => ({
                        id: p.id,
                        price: p.price
                    }));
                } catch (error) {
                    Alpine.store('notifications').error('Error loading prices');
                } finally {
                    this.isLoading = false;
                }
            },

            get progress() {
                return (this.currentStep / this.totalSteps) * 100;
            },

            get filteredMembers() {
                if (!this.searchQuery) return this.members;
                const query = this.searchQuery.toLowerCase();
                return this.members.filter(m =>
                    m.firstname.toLowerCase().includes(query) ||
                    m.lastname.toLowerCase().includes(query)
                );
            },

            get selectedMemberCount() {
                return this.selectedMembers.length;
            },

            get selectedProductName() {
                const product = this.products.find(p => p.id == this.selectedProduct);
                return product ? product.name : '';
            },

            get calculatedTotalPrice() {
                if (this.productTotalPrice) {
                    return parseFloat(this.productTotalPrice) || 0;
                }
                if (this.productPricePerPerson && this.selectedMemberCount > 0) {
                    return (parseFloat(this.productPricePerPerson) || 0) * this.selectedMemberCount;
                }
                return 0;
            },

            get calculatedPricePerPerson() {
                if (this.productPricePerPerson) {
                    return parseFloat(this.productPricePerPerson) || 0;
                }
                if (this.productTotalPrice && this.selectedMemberCount > 0) {
                    return (parseFloat(this.productTotalPrice) || 0) / this.selectedMemberCount;
                }
                return 0;
            },


            async onPriceSelect() {
                if (this.selectedPrice) {
                    this.productTotalPrice = '';
                    this.productPricePerPerson = '';

                    const priceData = this.pricesData.find(p => p.id == this.selectedPrice);
                    if (priceData) {
                        this.productPricePerPerson = priceData.price;
                        this.productDescription = priceData.description || '';
                    }
                } else {
                    this.productTotalPrice = '';
                    this.productPricePerPerson = '';
                    this.productDescription = '';
                }
            },

            async nextStep() {
                if (this.currentStep < this.totalSteps) {
                    // Load invoice prices when moving from step 1 to 2
                    if (this.currentStep === 1 && this.selectedProduct) {
                        await this.loadPrices();
                        this.productTotalPrice = '';
                        this.productPricePerPerson = '';
                        this.productDescription = '';
                        this.selectedPrice = '';
                    }

                    // Load existing invoice lines when moving from step 2 to 3
                    if (this.currentStep === 2 && this.selectedProduct) {
                        this.isLoading = true;
                        try {
                            // Reset all selections
                            this.selectedMembers = [];

                            // If editing an existing price, pre-select its members
                            if (this.selectedPrice) {
                                // Load specific invoice lines for the selected price
                                const specificLinesResponse = await http.get(`{{ url('fiscus/specificinvoicelines') }}/${this.selectedPrice}`);
                                specificLinesResponse.data.forEach(item => {
                                    if (!this.selectedMembers.includes(item.member_id)) {
                                        this.selectedMembers.push(item.member_id);
                                    }
                                });
                            }
                            // If adding a new price (no price selected), start with no members selected
                            // User will select which members to add
                        } catch (error) {
                            Alpine.store('notifications').error('Error loading invoice lines');
                        } finally {
                            this.isLoading = false;
                        }
                    }

                    this.currentStep++;
                }
            },

            previousStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            },

            selectAll() {
                this.filteredMembers.forEach(member => {
                    if (!this.selectedMembers.includes(member.id)) {
                        this.selectedMembers.push(member.id);
                    }
                });
            },

            deselectAll() {
                this.selectedMembers = [];
            },

            async deleteProduct() {
                if (!confirm('Are you sure you want to delete this product?')) {
                    return;
                }

                this.isLoading = true;
                try {
                    const data = {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE',
                        product_id: this.selectedProduct
                    };

                    const response = await http.post(`{{ url('fiscus') }}/${this.selectedProduct}`, data);

                    if (response.data.success) {
                        Alpine.store('notifications').success(response.data.message || 'Product deleted successfully');

                        setTimeout(() => {
                            window.location.href = '{{ url("fiscus") }}';
                        }, 1500);
                    }
                } catch (error) {
                    Alpine.store('notifications').error(error.response?.data?.message || 'Error deleting product');
                } finally {
                    this.isLoading = false;
                }
            },

            async finish() {
                this.isLoading = true;

                try {
                    const data = {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        isupdate: this.selectedPrice || '',
                        finalproductname: this.selectedProductName,
                        finalproductdescription: this.productDescription,
                        finaltotalprice: this.calculatedTotalPrice,
                        finalpriceperperson: this.calculatedPricePerPerson,
                        finalselectedmembers: this.selectedMemberCount,
                        member: this.selectedMembers
                    };

                    const response = await http.post(`{{ url('fiscus') }}/${this.selectedProduct}`, data);

                    if (response.data.success) {
                        Alpine.store('notifications').success(response.data.message || 'Fiscus entry updated successfully');

                        setTimeout(() => {
                            window.location.href = '{{ url("fiscus") }}';
                        }, 1500);
                    } else {
                        if (response.data.errors) {
                            Object.keys(response.data.errors).forEach(field => {
                                Alpine.store('notifications').error(`${field} is empty`);
                            });
                        }
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
                        Alpine.store('notifications').error(error.response?.data?.message || 'Error updating fiscus entry');
                    }
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@stop

@section('content')
<div x-data="fiscusEditWizard">
    <!-- Progress Bar -->
    <div class="progress mb-3">
        <div class="progress-bar" role="progressbar" :style="`width: ${progress}%`" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
    </div>

    <!-- Step Indicators -->
    <ul class="nav nav-pills nav-justified mb-3">
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 1 ? 'active' : ''">1. Select Product</span>
        </li>
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 2 ? 'active' : ''">2. Add/Edit Price</span>
        </li>
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 3 ? 'active' : ''">3. Select Members</span>
        </li>
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 4 ? 'active' : ''">4. Summary</span>
        </li>
    </ul>

    <!-- Step 1: Select Product -->
    <div x-show="currentStep === 1" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Select Product</h5>

            <div class="mb-2">
                <label class="form-label">Product Name</label>
                <select x-model="selectedProduct" class="form-control">
                    <option value="">Select a product</option>
                    <template x-for="product in products" :key="product.id">
                        <option :value="product.id" x-text="product.name"></option>
                    </template>
                </select>
            </div>

            <div class="mb-0">
                <button type="button" @click="deleteProduct" class="btn btn-danger" :disabled="isLoading || !selectedProduct">
                    Delete Product
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Add/Edit Price -->
    <div x-show="currentStep === 2" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Add/Edit Price</h5>

            <div class="mb-2">
                <label class="form-label">Change Price</label>
                <select x-model="selectedPrice" @change="onPriceSelect" class="form-control">
                    <option value="">Select a price</option>
                    <template x-for="price in availablePrices" :key="price.id">
                        <option :value="price.id" x-text="'€ ' + price.price"></option>
                    </template>
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label">New Total Price (€)</label>
                <input type="text" x-model="productTotalPrice" class="form-control" autocomplete="off">
            </div>

            <div class="mb-2">
                <label class="form-label">New Price per person</label>
                <input type="text" x-model="productPricePerPerson" class="form-control" autocomplete="off">
            </div>

            <div class="mb-0">
                <label class="form-label">Description</label>
                <textarea x-model="productDescription" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>

    <!-- Step 3: Select Members -->
    <div x-show="currentStep === 3" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Select Members</h5>

            <div class="mb-2">
                <label class="form-label">Search Member</label>
                <input type="text" x-model="searchQuery" class="form-control" autocomplete="off">
            </div>

            <div class="mb-2">
                <button type="button" @click="selectAll" class="btn btn-outline-primary me-2">
                    <i data-lucide="check"></i> Select All
                </button>
                <button type="button" @click="deselectAll" class="btn btn-outline-primary">
                    <i data-lucide="x"></i> Deselect All
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">Select</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="member in filteredMembers" :key="member.id">
                            <tr>
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        :value="member.id"
                                        x-model="selectedMembers">
                                </td>
                                <td x-text="member.firstname"></td>
                                <td x-text="member.lastname"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Step 4: Summary -->
    <div x-show="currentStep === 4" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Summary</h5>

            <div class="mb-2">
                <label class="form-label">Product Name</label>
                <input type="text" :value="selectedProductName" class="form-control" readonly>
            </div>

            <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea :value="productDescription" class="form-control" rows="3" readonly></textarea>
            </div>

            <div class="mb-2">
                <label class="form-label">Total Price</label>
                <input type="text" :value="'€ ' + calculatedTotalPrice.toFixed(2)" class="form-control" readonly>
            </div>

            <div class="mb-2">
                <label class="form-label">Price Per Person</label>
                <input type="text" :value="'€ ' + calculatedPricePerPerson.toFixed(2)" class="form-control" readonly>
            </div>

            <div class="mb-0">
                <label class="form-label">Total Selected Members</label>
                <input type="text" :value="selectedMemberCount" class="form-control" readonly>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between">
        <button type="button" @click="previousStep" x-show="currentStep > 1" class="btn btn-secondary">
            Previous
        </button>
        <button type="button" @click="nextStep" x-show="currentStep < totalSteps" class="btn btn-primary" :disabled="isLoading">
            <span x-text="isLoading ? 'Loading...' : 'Next'"></span>
        </button>
        <button type="button" @click="finish" x-show="currentStep === totalSteps" class="btn btn-success" :disabled="isLoading">
            <span x-text="isLoading ? 'Saving...' : 'Finish'"></span>
        </button>
    </div>
</div>
@stop
