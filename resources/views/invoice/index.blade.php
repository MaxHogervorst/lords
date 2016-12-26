@extends('layout.master')

@section('script')
    <script>
        $(document).ready(function(){
            $('#invoiceGroup').selectize({
                selectOnTab: true,
                dropdownParent: 'body'
            });

        $('#invoiceMonth').datepicker( {
                        format: "MM-yyyy",
                        viewMode: "months",
                        minViewMode: "months",
                        autoclose: true
                    });

        $('#newinvoicegroupbutton').click(function() {
            $("#newInvoiceGroupModal").modal('show');
            return false;
        })

        });
    </script>
@stop

@section('content')

    <form id="invoicegroupForm" method="post" action="invoice/selectinvoicegroup">
    <div class="row">
        <label for="inputEmail" class="col-lg-1 control-label">Select Month</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <select id="invoiceGroup" name="invoiceGroup" class="form-control"  autocomplete="off">
                        <option value="">Search and select month/option>
                        @foreach($invoicegroups as $i)
                            @if($i->status)
                                <option value={{ $i->id }}>Active Month: {{ $i->name}}</option>
                            @else
                                <option value={{ $i->id }}>{{ $i->name}}</option>
                            @endif
                        @endforeach
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#invoicegroupForm" data-ajax-callback-function="reload"><i class="fa fa-check fa-fw">  </i>Select </button>
                    <button id="newinvoicegroupbutton" class="btn btn-outline btn-primary" ><i class="fa fa-plus fa-fw">  </i>New</button>




                </span>
           </div>
        </div>
    </div>
    </form>

    <div class="row">&nbsp;</div>
    <a href="invoice/excel" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-excel-o fa-fw">  </i>Export to Excel</button></a>
    <a href="invoice/pdf" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-pdf-o fa-fw">  </i>Export to PDF</button></a>
    <a href="invoice/sepa" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-pdf-o fa-fw">  </i>Export to SEPA</button></a>
    <div class="row">&nbsp;</div>

     @foreach($members as $m)

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
                        <td>&euro;{{ money_format('%.2n', $price)  }}</td>
                    </tr>
                @endforeach

                 @foreach($m->groups()->where('invoice_group_id', '=', $currentmonth->id)->get() as $g)


                    <?php $totalprice = 0; ?>
                    @foreach($g->orders as $o)
                        <?php $totalprice += $o->amount * $products[$o->product_id]['price']    ; ?>
                    @endforeach
                    <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; ?>

                    <tr>
                        <td>{{ $g->name }}</td>
                        <td>Groupmembers: {{ $totalmebers }} Total price: &euro;{{ $totalprice }}</td>
                        <td>1</td>
                        <td>&euro;{{ money_format('%.2n', $price)  }}</td>
                    </tr>


                @endforeach

                @foreach($m->invoice_lines as $il)
                    @if($il->productprice->product->invoice_group_id == $currentmonth->id)
                        <?php $price = $il->productprice->price; $total += $price; ?>
                        <tr>
                            <td>{{ $il->productprice->product->name }}</td>
                            <td>{{ $il->productprice->description }}</td>
                            <td>1</td>
                            <td>&euro;{{ money_format('%.2n', $price)  }}</td>
                        </tr>
                    @endif
                @endforeach

            </tbody>
            <tfoot>
                <tr class="info">
                    <td colspan="3" align="right"><b>Total:</b></td>
                    <td align="left"><b>&euro;{{ money_format('%.2n', $total)}}</b></td>
                </tr>
            </tfoot>


        </table>
        <div class="row">&nbsp;</div>
        <div class="row">&nbsp;</div>
    @endforeach



@stop

@section('modal')

    <div id="newInvoiceGroupModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog" >
     		<div class="modal-content">
    			<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    				<h4 class="modal-title">New Month</h4>
    			</div>
    			<div class="modal-body">
    				<form id="NewInvoiceGroupForm"  class="form-inline" action="invoice/storeinvoicegroup" method="post">
    			        <div class="form-group">
    			            Select Month and year:
    						<input type="text" id="invoiceMonth" name="invoiceMonth" class="form-control" value="" >
    						<input type="hidden" name="_token" value="{{ csrf_token() }}">
    						<button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#NewInvoiceGroupForm" data-ajax-callback-function="reload"><i class="fa fa-save fa-fw">  </i>New Month</button>

    					</div>
    				</form>
    			</div>
    		</div><!-- /.modal-content -->
    	</div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@stop