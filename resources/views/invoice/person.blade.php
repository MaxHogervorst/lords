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

            // Initialize Flatpickr for month picker (if exists)
            const invoiceMonth = document.getElementById('invoiceMonth');
            if (invoiceMonth) {
                flatpickr(invoiceMonth, {
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: true,
                            dateFormat: "m-Y",
                            altFormat: "F Y"
                        })
                    ]
                });
            }
        });
    </script>
@stop

@section('content')


    <div class="row" x-data="{
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
        <form id="invoicegroupForm" method="post" action="invoice/setpersonalinvoicegroup">
        <label for="inputEmail" class="col-lg-1 form-label">Select Month</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <select id="invoiceGroup" name="invoiceGroup" class="form-control"  autocomplete="off">
                        <option value="">Search and select month</option>
                        @foreach($invoicegroups as $i)
                            @if($i->status)
                                <option value={{ $i->id }}>Active Month: {{ $i->name}}</option>
                            @else
                                <option value={{ $i->id }}>{{ $i->name}}</option>
                            @endif
                        @endforeach
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-outline-primary" @click="selectMonth"><i data-lucide="check"></i>Select </button>
                </span>
           </div>
        </div>
        </form>
    </div>
    <h2>Viewing: {{ $currentmonth->name }}</h2>

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
            <label for="inputEmail" class="col-lg-1 form-label">Lastname</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="text" name="name">
                </div>
            </div>
            <label for="inputEmail" class="col-lg-1 form-label">IBAN</label>
            <div class="col-sm-4">
                <div class="input-group">
                        <input type="text" name="iban">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-outline-primary" @click="lookupPerson"><i data-lucide="check"></i>Look Up </button>
                    </span>
                </div>
            </div>
        </form>
    @if( ! is_null($m))
    <div class="row">&nbsp;</div>



        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="4"><h3><b>{{ $m->firstname . ' ' . $m->lastname }}</b></h3></th>
                </tr>
                <tr>
                    <th>Product</th>
                    <th>Description</th>
                    <th class="col-sm-1">Amount</th>
                    <th class="col-sm-1">TotalPrice</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                @foreach($m->orders()->where('invoice_group_id', '=', $currentmonth->id)->get() as $o)
                    <?php $price = $o->amount * $products[$o->product_id]['price']; $total += $price; ?>
                    <tr>
                        <td> {{ $products[$o->product_id]['name'] }}</td>
                        <td> {{ $products[$o->product_id]['name'] }}</td>
                        <td> {{ $o->amount }}</td>
                        <td>&euro;{{ number_format($price, 2, ".", ",")  }}</td>
                    </tr>
                @endforeach

                 @foreach($m->groups()->where('invoice_group_id', '=', $currentmonth->id)->get() as $g)


                    <?php $totalprice = 0; ?>
                    @foreach($g->orders as $o)
                        <?php $totalprice += $o->amount * $products[$o->product_id]['price']; ?>
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
                <tr class="info">
                    <td colspan="3" align="right"><b>Total:</b></td>
                    <td align="left"><b>&euro;{{ number_format($total, 2, ".", ",")}}</b></td>
                </tr>
            </tfoot>


        </table>

        @endif


@stop
