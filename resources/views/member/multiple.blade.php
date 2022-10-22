

@section('content')

    <form id="add-groupmembers-form" class="form-inline" method="post" action="{{ url('group/addmember') }}">
        <div class="form-group">
            <select id="member-select" name="member" class="form-control">
                <option> Select Product</option>
                @foreach($members as $m)
                    <option value="{{ $m->id }}">{{ $m->firstname . ' ' . $m->lastname }}</option>
                @endforeach
            </select>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="groupid" value="{{ $group->id }}">
        </div>
        <button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#add-groupmembers-form" data-ajax-callback-function="addGroupMember" ><i class="fa fa-plus fa-fw">  </i>Add Member</button>

    </form>


@stop