@extends('layout.clean')

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TomSelect for invoice group dropdown
            const invoiceGroupSelect = document.getElementById('invoiceGroup');
            if (invoiceGroupSelect) {
                new TomSelect(invoiceGroupSelect, {
                    selectOnTab: true,
                    create: false
                });
            }
        });
    </script>
@stop

@section('content')
    <div class="text-center mb-4">
        <h1 class="display-5">Check Your Bill</h1>
        <p class="text-muted">View your personal invoice for GSRC Lords</p>
    </div>

    <!-- Month Selection Card -->
    <div class="card mb-3" x-data="{
        async selectMonth() {
            const formData = new FormData(document.getElementById('invoicegroupForm'));
            try {
                const response = await http.post('invoice/setpersonalinvoicegroup', formData);
                if (response.data.success) {
                    location.reload();
                }
            } catch (error) {
                Alpine.store('notifications').error('Error selecting month');
            }
        }
    }">
        <div class="card-header">
            <h3 class="card-title">Select Invoice Month</h3>
        </div>
        <div class="card-body">
            <form id="invoicegroupForm" method="post" action="invoice/setpersonalinvoicegroup">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row g-2">
                    <div class="col">
                        <select id="invoiceGroup" name="invoiceGroup" class="form-select" autocomplete="off">
                            <option value="">Search and select month</option>
                            @foreach($invoicegroups as $i)
                                @if($i->status)
                                    <option value="{{ $i->id }}" {{ $currentmonth->id == $i->id ? 'selected' : '' }}>Active Month: {{ $i->name }}</option>
                                @else
                                    <option value="{{ $i->id }}" {{ $currentmonth->id == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" @click="selectMonth">
                            <i data-lucide="check"></i>
                            Select
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Viewing:</strong> {{ $currentmonth->name }}
    </div>

    <!-- Person Lookup Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Lookup Your Invoice</h3>
        </div>
        <div class="card-body">
            <form id="invoicePersonGroup" method="post" action="invoice/setperson" x-data="{
                async lookupPerson() {
                    const formData = new FormData(document.getElementById('invoicePersonGroup'));
                    try {
                        const response = await http.post('invoice/setperson', formData);
                        if (response.data.success) {
                            location.reload();
                        }
                    } catch (error) {
                        Alpine.store('notifications').error('Error looking up person');
                    }
                }
            }">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Last Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter your last name"
                            autocomplete="off">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">IBAN</label>
                        <input
                            type="text"
                            name="iban"
                            class="form-control"
                            placeholder="Enter your IBAN"
                            autocomplete="off">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" @click="lookupPerson">
                            <i data-lucide="search"></i>
                            Look Up
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoice Details Card -->
    @if(!is_null($m))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Invoice for {{ $m->firstname . ' ' . $m->lastname }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>

                        {{-- Individual Orders --}}
                        @foreach($m->orders()->where('invoice_group_id', '=', $currentmonth->id)->get() as $o)
                            <?php $price = $o->amount * $products[$o->product_id]['price']; $total += $price; ?>
                            <tr>
                                <td>{{ $products[$o->product_id]['name'] }}</td>
                                <td>{{ $products[$o->product_id]['name'] }}</td>
                                <td class="text-end">{{ $o->amount }}</td>
                                <td class="text-end">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                            </tr>
                        @endforeach

                        {{-- Group Orders --}}
                        @foreach($m->groups()->where('invoice_group_id', '=', $currentmonth->id)->get() as $g)
                            <?php $totalprice = 0; ?>
                            @foreach($g->orders as $o)
                                <?php $totalprice += $o->amount * $products[$o->product_id]['price']; ?>
                            @endforeach
                            <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; ?>
                            <tr>
                                <td>{{ $g->name }}</td>
                                <td>
                                    <span class="text-muted">Groupmembers: {{ $totalmebers }}</span>
                                    <span class="text-muted">| Total price: &euro;{{ number_format($totalprice, 2, ".", ",") }}</span>
                                </td>
                                <td class="text-end">1</td>
                                <td class="text-end">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                            </tr>
                        @endforeach

                        {{-- Invoice Lines --}}
                        @foreach($m->invoice_lines as $il)
                            @if($il->productprice->product->invoice_group_id == $currentmonth->id)
                                <?php $price = $il->productprice->price; $total += $price; ?>
                                <tr>
                                    <td>{{ $il->productprice->product->name }}</td>
                                    <td>{{ $il->productprice->description }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">&euro;{{ number_format($total, 2, ".", ",") }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@stop
