 <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">{{ $product->name }}</h4>
</div>
<div class="modal-body">
    <form id="product-form-edit" name="product-form-edit" class="form-inline" action="{{ url('product/' . $product->id) }}" method="put">
        <div class="form-group">
            <input type="text" id="productname-edit" name="productName"  placeholder="Product name" value="{{ $product->name }}" class="form-control">
         </div>
         <div class="form-group">
            <input type="text" id="productprice-edit" name="productPrice"  placeholder="Product price" value="{{ $product->price }}" class="form-control">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </div>
        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="PUT" data-ajax-submit="#product-form-edit" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-save fa-fw">  </i>Save Changes</button>
    </form>
</div>
<div class="modal-footer">
    <form id="product-delete-form" name="product-delete-form" class="form-horizontal" action="{{ url('product/' . $product->id) }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="DELETE" data-ajax-submit="#product-delete-form" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-edit fa-fw">  </i>Delete Member</button>
    </form>
</div>