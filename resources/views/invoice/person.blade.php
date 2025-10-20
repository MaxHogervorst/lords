@extends('layout.clean')

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TomSelect for invoice group dropdown
            const invoiceGroupSelect = document.getElementById('invoiceGroup');
            if (invoiceGroupSelect) {
                new TomSelect(invoiceGroupSelect, {
                    selectOnTab: true,
                    create: false,
                    placeholder: 'Select invoice month'
                });
            }

            // Add loading state to form submission
            const lookupForm = document.getElementById('lookupForm');
            if (lookupForm) {
                lookupForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
                });
            }
        });
    </script>
@stop

@section('content')
    <div class="container-fluid" style="max-width: 1600px; margin: 0 auto;">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i data-lucide="file-text" class="icon icon-lg text-primary"></i>
            </div>
            <h1 class="display-5 fw-bold">Check Your Bill</h1>
            <p class="text-muted fs-4">View your personal invoice for GSRC Lords</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i data-lucide="alert-circle" class="icon me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <!-- Invoice Lookup Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0 fs-3">
                    <i data-lucide="search" class="icon me-2"></i>
                    Lookup Your Invoice
                </h3>
            </div>
            <div class="card-body p-4 p-md-5">
                <form id="lookupForm" method="post" action="{{ route('invoice.check-bill.post') }}" class="check-bill-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i data-lucide="user" class="icon icon-sm me-1"></i>
                                Last Name
                            </label>
                            <input
                                type="text"
                                name="name"
                                class="form-control form-control-lg @error('name') is-invalid @enderror"
                                placeholder="e.g., Smith"
                                value="{{ old('name') }}"
                                autocomplete="off"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Enter your family name as registered</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i data-lucide="credit-card" class="icon icon-sm me-1"></i>
                                IBAN
                            </label>
                            <input
                                type="text"
                                id="ibanInput"
                                name="iban"
                                class="form-control form-control-lg @error('iban') is-invalid @enderror"
                                placeholder="NL00 BANK 0123 4567 89"
                                value="{{ old('iban') }}"
                                autocomplete="off"
                                required>
                            @error('iban')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Your bank account number</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i data-lucide="calendar" class="icon icon-sm me-1"></i>
                                Invoice Month
                            </label>
                            <select id="invoiceGroup" name="invoiceGroup" class="form-select form-select-lg @error('invoiceGroup') is-invalid @enderror" autocomplete="off" required>
                                <option value="">Select month</option>
                                @foreach($invoicegroups as $i)
                                    @if($i->status)
                                        <option value="{{ $i->id }}" {{ (old('invoiceGroup') == $i->id || (!old('invoiceGroup') && $currentmonth->id == $i->id)) ? 'selected' : '' }}>
                                            ⭐ {{ $i->name }} (Active)
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
                            <small class="form-hint">Select the billing period</small>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i data-lucide="search" class="icon me-2"></i>
                            Look Up Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoice Details Card -->
        @if(!is_null($m))
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">
                            <i data-lucide="file-text" class="icon me-2"></i>
                            Invoice for {{ $m->firstname . ' ' . $m->lastname }}
                        </h3>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary fs-5 fw-semibold px-3 py-2">{{ $currentmonth->name }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover check-bill-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="fw-bold" style="width: 30%;">Product</th>
                                <th class="fw-bold description-column" style="width: 45%;">Description</th>
                                <th class="text-end fw-bold" style="width: 10%;">Amount</th>
                                <th class="text-end fw-bold" style="width: 15%;">Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; $hasItems = false; ?>

                            {{-- Individual Orders --}}
                            @foreach($m->orders->where('invoice_group_id', $currentmonth->id) as $o)
                                <?php $price = $o->amount * $products[$o->product_id]['price']; $total += $price; $hasItems = true; ?>
                                <tr>
                                    <td>
                                        <strong>{{ $products[$o->product_id]['name'] }}</strong>
                                    </td>
                                    <td class="text-muted description-column">{{ $products[$o->product_id]['name'] }}</td>
                                    <td class="text-end">
                                        {{ $o->amount }}
                                    </td>
                                    <td class="text-end fw-bold">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                                </tr>
                            @endforeach

                            {{-- Group Orders --}}
                            @foreach($m->groups->where('invoice_group_id', $currentmonth->id) as $g)
                                <?php $totalprice = 0; ?>
                                @foreach($g->orders as $o)
                                    <?php $totalprice += $o->amount * $products[$o->product_id]['price']; ?>
                                @endforeach
                                <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; $hasItems = true; ?>
                                <tr class="table-info">
                                    <td>
                                        <strong>{{ $g->name }}</strong>
                                    </td>
                                    <td class="description-column">
                                        <div class="d-flex flex-column gap-1">
                                            <small class="text-muted">
                                                {{ $totalmebers }} member{{ $totalmebers > 1 ? 's' : '' }}
                                            </small>
                                            <small class="text-muted">
                                                Group total: &euro;{{ number_format($totalprice, 2, ".", ",") }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-end text-muted">
                                        <small>Your share</small>
                                    </td>
                                    <td class="text-end fw-bold">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                                </tr>
                            @endforeach

                            {{-- Invoice Lines --}}
                            @foreach($m->invoice_lines as $il)
                                @if($il->productprice->product->invoice_group_id == $currentmonth->id)
                                    <?php $price = $il->productprice->price; $total += $price; $hasItems = true; ?>
                                    <tr>
                                        <td>
                                            <strong>{{ $il->productprice->product->name }}</strong>
                                        </td>
                                        <td class="text-muted description-column">{{ $il->productprice->description }}</td>
                                        <td class="text-end">
                                            1
                                        </td>
                                        <td class="text-end fw-bold">&euro;{{ number_format($price, 2, ".", ",") }}</td>
                                    </tr>
                                @endif
                            @endforeach

                            @if(!$hasItems)
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i data-lucide="inbox" class="icon icon-lg mb-3"></i>
                                        <p class="mb-0">No items found for this invoice period</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if($hasItems)
                            <tfoot class="bg-light">
                                <tr class="border-top border-2">
                                    <td colspan="3" class="text-end fw-bold fs-4">Total Amount Due:</td>
                                    <td class="text-end fw-bold text-primary check-bill-total" style="font-size: 1.75rem;">&euro;{{ number_format($total, 2, ".", ",") }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                @if($hasItems)
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i data-lucide="info" class="icon me-2"></i>
                                        <div>
                                            <strong>Payment Information:</strong> This amount will be automatically collected from your registered IBAN account.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@stop
