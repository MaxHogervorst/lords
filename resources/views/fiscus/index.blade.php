@extends('layout.master')


@section('content')
    @php
        $productsData = ($invoice_products ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values();
    @endphp
    <div x-data="fiscusManager(@js($productsData))" x-cloak>
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
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="product in filteredProducts" :key="product.id">
                            <tr>
                                <td x-text="product.name"></td>
                                <td class="text-nowrap">
                                    <a :href="`{{ url('fiscus/edit') }}?product=${product.id}`" class="btn btn-sm btn-ghost-primary">
                                        <i data-lucide="edit"></i>
                                    </a>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredProducts.length === 0">
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    No products found
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop