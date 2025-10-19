@extends('layout.export')
@section('head')
<style>

.pure-table-horizontal td,
.pure-table-horizontal th {
    border-width: 0 0 1px 0;
    border-bottom: 1px solid #cbcbcb;
    padding:0;
}
.pure-table-horizontal tbody > tr:last-child td {
    border-bottom-width: 0;
}
.pure-table-horizontal tfoot {
    background-color: #f2f2f2;

}.pure-table-horizontal tfoot td {
     border-bottom: 0px
    border-style: solid;
     border-top: 2px solid #5f5f5f;

}

</style>

@stop

@section('content')
@foreach($members as $m)

       <table class="pure-table-horizontal" width="730px" cellspacing="0" cellpadding="0" align="center">
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
               @foreach($m->orders->where('invoice_group_id', $currentmonth->id) as $o)
                   <?php $price = $o->amount * $o->product->price; $total += $price; ?>
                   <tr>
                       <td> {{ $o->product->name }}</td>
                       <td> {{ $o->product->name }}</td>
                       <td> {{ $o->amount }}</td>
                       <td>&euro;{{ $price }}</td>
                   </tr>
               @endforeach

                @foreach($m->groups->where('invoice_group_id', $currentmonth->id) as $g)

                   <?php $totalprice = 0; ?>
                   @foreach($g->orders as $o)
                       <?php $totalprice += $o->amount * $o->product->price; ?>
                   @endforeach
                   <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; ?>

                   <tr>
                       <td>{{ $g->name }}</td>
                       <td>Groupmembers: {{ $totalmebers }} Total price: &euro;{{ $totalprice }}</td>
                       <td>1</td>
                       <td>&euro;{{ $price }}</td>
                   </tr>

               @endforeach

               @foreach($m->invoice_lines as $il)
                   @if($il->productprice->product->invoice_group_id == $currentmonth->id)
                       <?php $price = $il->productprice->price; $total += $price; ?>
                       <tr>
                           <td>{{ $il->productprice->product->name }}</td>
                           <td>{{ $il->productprice->description }}</td>
                           <td>1</td>
                           <td>&euro;{{ $price }}</td>
                       </tr>
                   @endif
               @endforeach

           </tbody>
           <tfoot>
               <tr class="info">
                   <td colspan="3" align="right"><b>Total:</b></td>
                   <td align="left"><b>&euro;{{ number_format((float)$total,2) }}</b></td>
               </tr>
           </tfoot>


       </table>
       <div class="row">&nbsp;</div>
       <div class="row">&nbsp;</div>
   @endforeach

@stop