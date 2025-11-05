<!-- Product Details Section -->
<div class="mb-3">
    <h6 class="mb-2 fw-bold">Product Details</h6>

    <div class="mb-2">
        <label class="form-label required mb-1">Product Name</label>
        <input type="text"
               x-model="form.productName"
               class="form-control form-control-sm"
               placeholder="e.g., Dinner, Drinks"
               autocomplete="off">
    </div>

    <div class="mb-2">
        <label class="form-label mb-1">Description</label>
        <textarea x-model="form.productDescription"
                  class="form-control form-control-sm"
                  rows="2"
                  placeholder="Optional description"></textarea>
    </div>
</div>

<!-- Pricing Section -->
<div class="mb-3">
    <h6 class="mb-2 fw-bold">Pricing <span class="text-muted small fw-normal">(choose one)</span></h6>

    <div class="row">
        <div class="col-6 mb-2">
            <label class="form-label mb-1">Total Price (€)</label>
            <input type="number"
                   step="0.01"
                   x-model="form.productTotalPrice"
                   @input="calculatePricePerPerson"
                   :disabled="!!form.productPricePerPerson"
                   class="form-control form-control-sm"
                   placeholder="0.00"
                   autocomplete="off">
        </div>

        <div class="col-6 mb-2">
            <label class="form-label mb-1">Per Person (€)</label>
            <input type="number"
                   step="0.01"
                   x-model="form.productPricePerPerson"
                   @input="calculateTotalPrice"
                   :disabled="!!form.productTotalPrice"
                   class="form-control form-control-sm"
                   placeholder="0.00"
                   autocomplete="off">
        </div>
    </div>
</div>

<!-- Summary Section - Compact -->
<div class="alert alert-info mb-3 py-2">
    <div class="row text-center">
        <div class="col-4">
            <small class="d-block text-muted">Members</small>
            <strong x-text="selectedMemberCount"></strong>
        </div>
        <div class="col-4">
            <small class="d-block text-muted">Per Person</small>
            <strong x-text="'€' + calculatedPricePerPerson.toFixed(2)"></strong>
        </div>
        <div class="col-4">
            <small class="d-block text-muted">Total</small>
            <strong x-text="'€' + calculatedTotalPrice.toFixed(2)"></strong>
        </div>
    </div>
</div>

<!-- Members Section -->
<div class="mb-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-bold">Select Members</h6>
        <div>
            <button type="button"
                    @click="selectAllMembers"
                    class="btn btn-sm btn-outline-primary py-0 px-2 me-1">
                All
            </button>
            <button type="button"
                    @click="deselectAllMembers"
                    class="btn btn-sm btn-outline-secondary py-0 px-2">
                Clear
            </button>
        </div>
    </div>

    <input type="search"
           x-model="form.memberSearchQuery"
           class="form-control form-control-sm mb-2"
           placeholder="Search members"
           autocomplete="off">

    <div class="table-responsive" style="max-height: 250px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 0.25rem;">
        <table class="table table-sm table-hover mb-0">
            <thead class="sticky-top bg-white" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <tr>
                    <th width="30" class="py-1">
                        <input type="checkbox"
                               @change="$event.target.checked ? selectAllMembers() : deselectAllMembers()"
                               :checked="form.selectedMembers.length === filteredMembers.length && filteredMembers.length > 0">
                    </th>
                    <th class="py-1">Name</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="member in filteredMembers" :key="member.id">
                    <tr :class="form.selectedMembers.includes(member.id) ? 'table-active' : ''">
                        <td class="py-1">
                            <input type="checkbox"
                                   :value="member.id"
                                   x-model="form.selectedMembers"
                                   @change="recalculatePrices">
                        </td>
                        <td class="py-1" x-text="member.firstname + ' ' + member.lastname"></td>
                    </tr>
                </template>

                <template x-if="filteredMembers.length === 0">
                    <tr>
                        <td colspan="2" class="text-center text-muted py-2">
                            No members found
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
