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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!is_null($m))
        <div class="alert alert-info">
            <strong>Viewing:</strong> {{ $currentmonth->name }} - {{ $m->firstname . ' ' . $m->lastname }}
        </div>
    @endif

    <!-- Invoice Lookup Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Lookup Your Invoice</h3>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('invoice.check-bill.post') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Enter your last name"
                            value="{{ old('name') }}"
                            autocomplete="off"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IBAN</label>
                        <input
                            type="text"
                            name="iban"
                            class="form-control @error('iban') is-invalid @enderror"
                            placeholder="Enter your IBAN"
                            value="{{ old('iban') }}"
                            autocomplete="off"
                            required>
                        @error('iban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice Month</label>
                        <select id="invoiceGroup" name="invoiceGroup" class="form-select @error('invoiceGroup') is-invalid @enderror" autocomplete="off" required>
                            <option value="">Select month</option>
                            @foreach($invoicegroups as $i)
                                @if($i->status)
                                    <option value="{{ $i->id }}" {{ (old('invoiceGroup') == $i->id || (!old('invoiceGroup') && $currentmonth->id == $i->id)) ? 'selected' : '' }}>
                                        Active: {{ $i->name }}
                                    </option>
                                @else
                                    <option value="{{ $i->id }}" {{ old('invoiceGroup') == $i->id ? 'selected' : '' }}>
                                        {{ $i->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('invoiceGroup')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search"></i>
                        Look Up Invoice
                    </button>
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
