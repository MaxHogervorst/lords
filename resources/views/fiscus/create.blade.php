@extends('layout.master')
@section('script')
<script>
    // Alpine.js component for Fiscus Create wizard
    document.addEventListener('alpine:init', () => {
        Alpine.data('fiscusWizard', () => ({
            currentStep: 1,
            totalSteps: 3,
            isLoading: false,

            // Form data
            productName: '',
            productTotalPrice: '',
            productPricePerPerson: '',
            productDescription: '',
            searchQuery: '',
            members: @json($members),
            selectedMembers: [],

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

            nextStep() {
                if (this.currentStep < this.totalSteps) {
                    // Validation for step 2 (Select Members)
                    if (this.currentStep === 2 && this.selectedMembers.length === 0) {
                        Alpine.store('notifications').error('Select at least 1 person');
                        return;
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
                this.selectedMembers = this.filteredMembers.map(m => m.id);
            },

            deselectAll() {
                this.selectedMembers = [];
            },

            async finish() {
                this.isLoading = true;

                try {
                    const data = {
                        _token: '{{ csrf_token() }}',
                        finalproductname: this.productName,
                        finalproductdescription: this.productDescription,
                        finaltotalprice: this.calculatedTotalPrice,
                        finalpriceperperson: this.calculatedPricePerPerson,
                        finalselectedmembers: this.selectedMemberCount,
                        member: this.selectedMembers
                    };

                    const response = await http.post('{{ url("fiscus") }}', data);

                    if (response.data.success) {
                        Alpine.store('notifications').success(response.data.message || 'Fiscus entry created successfully');

                        // Redirect after success
                        setTimeout(() => {
                            window.location.href = '{{ url("fiscus") }}';
                        }, 1500);
                    } else {
                        // Handle validation errors
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
                        Alpine.store('notifications').error(error.response?.data?.message || 'Error creating fiscus entry');
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
<div x-data="fiscusWizard">
    <!-- Progress Bar -->
    <div class="progress mb-3">
        <div class="progress-bar" role="progressbar" :style="`width: ${progress}%`" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
    </div>

    <!-- Step Indicators -->
    <ul class="nav nav-pills nav-justified mb-3">
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 1 ? 'active' : ''">1. Add Product</span>
        </li>
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 2 ? 'active' : ''">2. Select Members</span>
        </li>
        <li class="nav-item">
            <span class="nav-link" :class="currentStep >= 3 ? 'active' : ''">3. Summary</span>
        </li>
    </ul>

    <!-- Step 1: Add Product -->
    <div x-show="currentStep === 1" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Add Product</h5>

            <div class="mb-2">
                <label class="form-label">Product Name</label>
                <input type="text" x-model="productName" class="form-control" autocomplete="off">
            </div>

            <div class="mb-2">
                <label class="form-label">Total Price (€)</label>
                <input type="text" x-model="productTotalPrice" class="form-control" autocomplete="off">
            </div>

            <div class="mb-2">
                <label class="form-label">Price per person</label>
                <input type="text" x-model="productPricePerPerson" class="form-control" autocomplete="off">
            </div>

            <div class="mb-0">
                <label class="form-label">Description</label>
                <textarea x-model="productDescription" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>

    <!-- Step 2: Select Members -->
    <div x-show="currentStep === 2" class="card mb-3">
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
                                    <input type="checkbox" :value="member.id" x-model="selectedMembers">
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

    <!-- Step 3: Summary -->
    <div x-show="currentStep === 3" class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Summary</h5>

            <div class="mb-2">
                <label class="form-label">Product Name</label>
                <input type="text" :value="productName" class="form-control" readonly>
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
