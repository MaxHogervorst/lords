<div x-data="groupOrderModal" x-show="$store.modals.orderModal.type === 'group'">
    <div class="modal-header">
        <h5 class="modal-title" x-text="$store.modals.orderModal.entity?.name || ''"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="#" class="nav-link" :class="{ active: $store.modals.orderModal.activeTab === 'orders' }" @click.prevent="$store.modals.orderModal.activeTab = 'orders'" role="tab" data-testid="tab-orders">Orders</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#" class="nav-link" :class="{ active: $store.modals.orderModal.activeTab === 'groupmembers' }" @click.prevent="$store.modals.orderModal.activeTab = 'groupmembers'" role="tab" data-testid="tab-groupmembers">Group Members</a>
            </li>
        </ul>

        <!-- Orders Tab -->
        <div x-show="$store.modals.orderModal.activeTab === 'orders'">
            <form id="order-form" action="{{ url('order/store/Group') }}" @submit.prevent="saveOrder" class="mb-4">
                <div class="row g-2">
                    <div class="col">
                        <input type="number" id="amount" name="amount" value="1" min="1" autocomplete="off" class="form-control" placeholder="Amount" data-testid="order-amount-input">
                    </div>
                    <div class="col">
                        <select id="product-select" name="product" class="form-select" data-testid="order-product-select">
                            <option value="">Select Product</option>
                            <template x-for="product in $store.modals.orderModal.products" :key="product.id">
                                <option :value="product.id" x-text="product.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" :disabled="isLoading" class="btn btn-primary" data-testid="order-submit-button">
                            <i data-lucide="plus"></i>
                            <span x-text="isLoading ? 'Saving...' : 'Add'"></span>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="memberId" :value="$store.modals.orderModal.entity?.id">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>

            <h6 class="mb-3">Order History</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in $store.modals.orderModal.orders" :key="order.id">
                            <tr>
                                <td x-text="order.created_at"></td>
                                <td x-text="order.product_name"></td>
                                <td x-text="order.amount"></td>
                            </tr>
                        </template>
                        <template x-if="$store.modals.orderModal.orders.length === 0">
                            <tr>
                                <td colspan="3" class="text-center text-muted">No orders yet</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <h6 class="mb-3">Order Totals</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="total in $store.modals.orderModal.orderTotals" :key="total.product_id">
                            <tr>
                                <td x-text="total.product_name"></td>
                                <td x-text="total.count"></td>
                            </tr>
                        </template>
                        <template x-if="$store.modals.orderModal.orderTotals.length === 0">
                            <tr>
                                <td colspan="2" class="text-center text-muted">No totals yet</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Group Members Tab -->
        <div x-show="$store.modals.orderModal.activeTab === 'groupmembers'">
            <form id="add-groupmembers-form" @submit.prevent="addGroupMember" class="mb-4">
                <div class="row g-2">
                    <div class="col">
                        <select id="member-select" name="member" class="form-select" data-testid="group-member-select">
                            <option value="">Select Member</option>
                            <template x-for="member in $store.modals.orderModal.members" :key="member.id">
                                <option :value="member.id" x-text="member.firstname + ' ' + member.lastname"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" :disabled="isLoading" class="btn btn-primary" data-testid="group-member-submit-button">
                            <i data-lucide="plus"></i>
                            <span x-text="isLoading ? 'Adding...' : 'Add Member'"></span>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="groupid" :value="$store.modals.orderModal.entity?.id">
            </form>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="member in $store.modals.orderModal.groupMembers" :key="member.pivot_id">
                            <tr>
                                <td x-text="member.firstname + ' ' + member.lastname"></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-ghost-danger" @click.prevent="deleteGroupMember(member.pivot_id)" :disabled="isLoading" :data-testid="'delete-group-member-' + member.pivot_id">
                                        <i data-lucide="trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="$store.modals.orderModal.groupMembers.length === 0">
                            <tr>
                                <td colspan="2" class="text-center text-muted">No members yet</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>