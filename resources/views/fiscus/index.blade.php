@extends('layout.master')


@section('content')
    @php
        $productsData = ($invoice_products ?? collect())->map(function($p) {
            $price = $p->productprice->first();
            $members = $price ? $price->invoiceline->pluck('member')->filter() : collect();
            $memberCount = $members->count();
            $pricePerPerson = $price ? $price->price : 0;
            $totalPrice = $pricePerPerson * $memberCount;

            return [
                'id' => $p->id,
                'name' => $p->name,
                'member_count' => $memberCount,
                'price_per_person' => $pricePerPerson,
                'total_price' => $totalPrice
            ];
        })->values();

        $membersData = ($members ?? collect())->map(fn($m) => ['id' => $m->id, 'firstname' => $m->firstname, 'lastname' => $m->lastname])->values();
    @endphp
    <div x-data="fiscusManager(@js($productsData), @js($membersData))" x-cloak>
        <!-- Create Button -->
        <div class="mb-3">
            <button @click="openCreate" class="btn btn-primary">
                <i data-lucide="plus"></i> Create New Product
            </button>
        </div>

        <!-- Search Form -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col">
                        <input
                            type="search"
                            x-model="searchQuery"
                            x-ref="searchInput"
                            placeholder="Search products"
                            class="form-control"
                            autofocus
                            autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-center" style="width: 80px;">Members</th>
                            <th class="text-end" style="width: 120px;">Per Person</th>
                            <th class="text-end" style="width: 120px;">Total</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="product in filteredProducts" :key="product.id">
                            <tr>
                                <td x-text="product.name"></td>
                                <td class="text-center">
                                    <span x-text="product.member_count" class="badge bg-blue-lt"></span>
                                </td>
                                <td class="text-end" x-text="'€' + parseFloat(product.price_per_person).toFixed(2)"></td>
                                <td class="text-end" x-text="'€' + parseFloat(product.total_price).toFixed(2)"></td>
                                <td class="text-nowrap">
                                    <button @click="openEdit(product.id)" class="btn btn-sm btn-ghost-primary">
                                        <i data-lucide="edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredProducts.length === 0">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No products found
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Centered -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-backdrop"
             @click="close">
        </div>

        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="modal-centered"
             @click.stop>

            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'create' ? 'Create Product' : 'Edit Product'"></h3>
                <button @click="close" class="btn-close" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @include('fiscus.partials._form')
            </div>

            <div class="modal-footer">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <button x-show="mode === 'edit'"
                                @click="deleteProduct"
                                class="btn btn-danger btn-sm"
                                :disabled="isLoading">
                            <i data-lucide="trash-2"></i>
                            <span x-text="isLoading ? 'Deleting...' : 'Delete'"></span>
                        </button>
                    </div>
                    <div>
                        <button @click="close" class="btn btn-secondary me-2" :disabled="isLoading">Cancel</button>
                        <button @click="save" class="btn btn-primary" :disabled="isLoading">
                            <span x-text="isLoading ? 'Saving...' : 'Save'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-centered {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            z-index: 1051;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid #e9ecef;
            flex-shrink: 0;
        }
    </style>
@stop