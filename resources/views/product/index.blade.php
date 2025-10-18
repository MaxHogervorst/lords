@extends('layout.master')

@section('content')
    <div x-data='productsManager(@json($results))' x-cloak>
        <!-- Search and Add Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form x-ref="addProductForm" @submit.prevent="addProduct" action="{{ url('product') }}" method="post">
                    <div class="row g-2">
                        <div class="col">
                            <input
                                type="search"
                                x-ref="searchInput"
                                id="filter"
                                name="name"
                                x-model="searchQuery"
                                placeholder="Search or Add"
                                class="form-control"
                                autofocus
                                autocomplete="off">
                        </div>
                        <div class="col">
                            <input
                                type="text"
                                id="productprice"
                                name="productPrice"
                                @keypress="validateNumber($event, true)"
                                placeholder="Product price"
                                class="form-control"
                                autocomplete="off">
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-auto">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                data-testid="add-product-button"
                                :disabled="$store.app.isLoading">
                                <i data-lucide="plus"></i>
                                <span x-text="$store.app.isLoading ? 'Adding...' : 'Add Product'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="products">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="product in filteredProducts" :key="product.id">
                            <tr :id="product.id">
                                <td x-text="product.name"></td>
                                <td x-text="product.price"></td>
                                <td class="text-nowrap">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-ghost-primary"
                                        :data-testid="'product-edit-' + product.id"
                                        :data-id="product.id"
                                        @click="openEditModal(product.id)">
                                        <i data-lucide="edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredProducts.length === 0">
                            <tr>
                                <td colspan="3" class="text-center text-muted">No products found</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('modal')
<div class="modal modal-blur fade" id="product-edit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @include('product.edit')
        </div>
    </div>
</div>
@stop
