<script>
    $(document).ready(function(){
        $('#product-select').selectize();
        $('#amount').bootstrapNumber();
    })

</script>
<tr><td></td><td></td></tr>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">{{ $member->firstname . ' ' . $member->lastname }}</h4>
</div>
<div class="modal-body">
    <div id="errorsModal"></div>
    <form id="order-form" class="form-inline" method="post" action="{{ url('order/store/Member') }}">
        <div class="form-group">
            <input type="number" id="amount" name="amount" value="1"  class="form-control" autocomplete="off">
            <select id="product-select" name="product" class="form-control" width="100% ">
                    <option> Select Product</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
            <input type="Hidden" id="memberId" name="memberId"  value="{{ $member->id }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

        </div>
          <button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#order-form" data-ajax-callback-function="addOrder" ><i class="fa fa-plus fa-fw">  </i>Save</button>
    </form>
    <div class="row">&nbsp;</div>
    <div class="scrollable">
        <table id="order-history" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th> Product </th>
                    <th> Amount </th>
                </tr>
            </thead>
            @foreach($member->orders()->where('invoice_group_id', '=', $currentmonth->id)->get() as $o)
                <tr>
                    <td>{{ $o->created_at }}</td>
                    <td>{{ $o->product->name }}</td>
                    <td>{{ $o->amount }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="row">&nbsp;</div>
    <table id="order-totals" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th> Product </th>
                <th> Amount </th>
            </tr>
        </thead>
        @foreach($member->orders()->where('invoice_group_id', '=', $currentmonth->id)->selectRaw('orders.product_id, count(orders.id) as count')->groupby('product_id')->get() as $total)
            <tr id="{{$total->product->id}}">
                <td> {{ $total->product->name }}</td>
                <td> {{ $total->count }}</td>
            </tr>

        @endforeach

    </table>
</div>
