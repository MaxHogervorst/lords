
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">{{ $member->firstname . ' ' . $member->lastname }}</h4>
</div>

<div class="modal-body">
    <form id="member-edit-form" name="product-form-edit" role="form" class="form-group" action="{{ url('member/' . $member->id) }}" method="post">
        <div class="form-group">
            <label for="memberName" class="control-label">First Name</label>
            <input type="text" id="memberName" name="name" class="form-control" value="{{ $member->firstname }}" >
        </div>
        <div class="form-group">
            <label for="memberLastName" class="control-label">last Name</label>
            <input type="text" id="memberLastName" name="lastname" class="form-control" value="{{ $member->lastname }}" >
        </div>
        <div class="form-group">
            <label for="bic" class="control-label">BIC</label>
            <input type="text" id="bic" name="bic" class="form-control" value="{{ $member->bic }}" >
        </div>
        <div class="form-group">
            <label for="iban" class="control-label">IBAN</label>
            <input type="text" id="iban" name="iban" class="form-control" value="{{ $member->iban }}" >
        </div>
        <div class="form-group">
            <label for="had_collection" class="control-label">Had Collection</label>
            <?php $checked = ''; ?>
            @if($member->had_collection )
                <?php $checked = 'checked';?>
            @endif
            <input type="checkbox" {{ $checked }} id="had_collection" name="had_collection" class="form-control"  >
        </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">


        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="PUT" data-ajax-submit="#member-edit-form" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-save fa-fw">  </i>Save Changes</button>
        </form>

</div>
<div class="modal-footer">
    <form id="member-delete-form" name="member-delete-form" class="form-horizontal" action="{{ url('member/' . $member->id) }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="DELETE" data-ajax-submit="#member-delete-form" data-ajax-callback-function="afterRefreshMessage"><i class="fa fa-edit fa-fw">  </i>Delete Member</button>
    </form>
    </form>
</div>