<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">{{ $group->name }}</h4>
</div>

<div class="modal-body">
    <form id="member-edit-form" name="product-form-edit" class="form-inline" action="{{ url('group/' . $group->id) }}" method="post">
        <div class="form-group">
            <input type="text" id="memberName" name="name" class="form-control" value="{{ $group->name }}" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

        </div>
            <button type="button" class="btn btn-outline btn-primary" data-ajax-type="PUT" data-ajax-submit="#member-edit-form" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-save fa-fw">  </i>Save Changes</button>
        </form>

</div>
<div class="modal-footer">
    <form id="member-delete-form" name="member-delete-form" class="form-horizontal" action="{{ url('group/' . $group->id) }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="DELETE" data-ajax-submit="#member-delete-form" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-edit fa-fw">  </i>Delete Member</button>
    </form>
    </form>
</div>