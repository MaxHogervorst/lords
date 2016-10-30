@extends('layout.master')

@section('script')
<script>
    $(document).ready(function(){
         $('#groupDate').datepicker({
                    todayHighlight: true,
                    format: "dd-mm-yyyy"
               });

    });

    $('#member-order').on('show.bs.modal', function (event) {
      $('#memberordermodalcontent').load('{{ url('group/') }}/'+ $(event.relatedTarget).data('id'))
    });

    $('#member-edit').on('show.bs.modal', function (event) {
              $('#membereditmodalcontent').load('{{ url('group/') }}/'+ $(event.relatedTarget).data('id') + '/edit')
            });

    function addGroup(data)
    {
        $('#members').prepend('<tr>  <td>' + data.name + '</td> <td><button class="btn-order" data-id="' + data.id + '" data-toggle="modal" data-target="#member-order"><i class="fa fa-plus fa-fw fa-lg"></i></button> <button data-id="' + data.id + '" data-toggle="modal" data-target="#member-edit"><i class="fa fa-edit fa-fw">  </i></button></td> </tr>');
        $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
    }



</script>
@stop


@section('content')

<div class="row">&nbsp;</div>


    <form id="member-form" name="member-form" class="form-inline" action="{{ url('group') }}" method="post">
        <input type="search" id="filter" name="name" placeholder="Search or Add" class="form-control" autofocus="" autocomplete="off">
        <input type="text" id="groupDate" name="groupdate" class="form-control"   autocomplete="off" value="{{ $results[1] }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="button" class="btn btn-outline btn-primary" data-ajax-type="POST" data-ajax-submit="#member-form" data-ajax-callback-function="addGroup"><i class="fa fa-plus fa-fw">  </i>Add Group</button>
    </form>

    <div class="row">&nbsp;</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="members">

            <thead>
                <tr>
                    <th>Group Name</th>
                    <th class="col-sm-1">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($results[0] as $m)
                <tr>
                    <td>{{ $m->name }}</td>
                    <td>
                        <button class="btn-order" data-id="{{ $m->id }}" data-toggle="modal" data-target="#member-order"><i class="fa fa-plus fa-fw fa-lg"></i></button>
                        <button data-id="{{ $m->id }}" data-toggle="modal" data-target="#member-edit"><i class="fa fa-edit fa-fw">  </i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>


</div>
@stop

@section('modal')
<div id="member-order" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" >
 		<div class="modal-content">
			<div id="memberordermodalcontent"></div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="member-edit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" >
 		<div class="modal-content">
            <div id="membereditmodalcontent"></div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@stop



