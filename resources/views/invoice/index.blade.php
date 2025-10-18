@extends('layout.master')

@section('content')
    <div x-data="invoiceManager()" x-cloak>
        <!-- Month Selection -->
        <div class="card mb-3">
            <div class="card-body">
                <form x-ref="selectInvoiceForm" @submit.prevent="selectInvoiceGroup" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <label for="invoiceGroup" class="form-label">Select Month</label>
                    <div class="row g-2">
                        <div class="col">
                            <select x-ref="invoiceGroupSelect" id="invoiceGroup" name="invoiceGroup" autocomplete="off" class="form-select">
                                <option value="">Search and select month</option>
                                @foreach($invoicegroups as $i)
                                    @if($i->status)
                                        <option value="{{ $i->id }}">Active Month: {{ $i->name}}</option>
                                    @else
                                        <option value="{{ $i->id }}">{{ $i->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="$store.app.isLoading">
                                <i data-lucide="check"></i>
                                <span x-text="$store.app.isLoading ? 'Selecting...' : 'Select'"></span>
                            </button>
                        </div>
                        <div class="col-auto">
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="const modal = new bootstrap.Modal(document.getElementById('newInvoiceGroupModal')); modal.show();">
                                <i data-lucide="plus"></i> New
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-auto">
                        <a href="invoice/excel" target="_blank" class="btn btn-outline-success">
                            <i data-lucide="file-spreadsheet"></i> Export to Excel
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="invoice/pdf" target="_blank" class="btn btn-outline-danger">
                            <i data-lucide="file-text"></i> Export to PDF
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="invoice/sepa" target="_blank" class="btn btn-outline-primary">
                            <i data-lucide="file-text"></i> Export to SEPA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Member Invoices -->
        @foreach($members as $m)

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $m->firstname . ' ' . $m->lastname }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>TotalPrice</th>
                        </tr>
                    </thead>
            <tbody>
                <?php $total = 0; ?>
                @foreach($m->orders as $o)
                    @if(isset($products[$o->product_id]))
                        <?php $price = $o->amount * $products[$o->product_id]['price']; $total += $price; ?>
                        <tr>
                            <td> {{ $products[$o->product_id]['name'] }}</td>
                            <td> {{ $products[$o->product_id]['name'] }}</td>
                            <td> {{ $o->amount }}</td>
                            <td>&euro;{{ number_format($price, 2, ".", ",")  }}</td>
                        </tr>
                    @else
                        <tr>
                            <td> deleted product</td>
                            <td> deleted product</td>
                            <td> {{ $o->amount }}</td>
                            <td>&euro;deleted product</td>
                        </tr>
                    @endif
                @endforeach

                @foreach($m->groups as $g)
                        <?php $totalprice = 0; ?>


                        @foreach($g->orders as $o)
                            @if(isset($products[$o->product_id]))
                                <?php $totalprice += $o->amount * $products[$o->product_id]['price']; ?>
                            @endif
                        @endforeach


                    <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; ?>

                    <tr>
                        <td>{{ $g->name }}</td>
                        <td>Groupmembers: {{ $totalmebers }} Total price: &euro;{{ $totalprice }}</td>
                        <td>1</td>
                        <td>&euro;{{ number_format($price, 2, ".", ",")  }}</td>
                    </tr>


                @endforeach

                @foreach($m->invoice_lines as $il)
                    @if($il->productprice->product->invoice_group_id == $currentmonth->id)
                        <?php $price = $il->productprice->price; $total += $price; ?>
                        <tr>
                            <td>{{ $il->productprice->product->name }}</td>
                            <td>{{ $il->productprice->description }}</td>
                            <td>1</td>
                            <td>&euro;{{ number_format($price, 2, ".", ",")  }}</td>
                        </tr>
                    @endif
                @endforeach

            </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>&euro;{{ number_format($total, 2, ".", ",")}}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach

        <!-- Pagination -->
        @if($members->hasPages())
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="me-2 mb-0">Show:</label>
                        <select id="per_page" class="form-select form-select-sm w-auto" onchange="window.location.href='?per_page=' + this.value">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ms-2 text-muted">per page</span>
                    </div>
                    <div>
                        {{ $members->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

@stop

@section('modal')
<div class="modal modal-blur fade" id="newInvoiceGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" x-data="invoiceManager">
            <div class="modal-header">
                <h5 class="modal-title">New Month</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="new-month-form" x-ref="newMonthForm" @submit.prevent="createNewMonth" method="post">
                    <div class="mb-3">
                        <label for="invoiceMonth" class="form-label">Select Month and year:</label>
                        <input
                            type="text"
                            x-ref="invoiceMonthPicker"
                            id="invoiceMonth"
                            name="invoiceMonth"
                            class="form-control"
                            required>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    form="new-month-form"
                    class="btn btn-primary"
                    :disabled="$store.app.isLoading">
                    <i data-lucide="save"></i>
                    <span x-text="$store.app.isLoading ? 'Creating...' : 'New Month'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@stop
