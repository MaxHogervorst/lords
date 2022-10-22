@extends('layout.master')
@section('script')
    <script>
        $(document).ready(function(){
            $("#bankinfo").change(function() {
                if(this.checked) {
                    $('#members tbody tr').each(function() {
                        if ($(this).find('td:empty').length == 0) $(this).hide();
                    });
                }
                else{
                    $('#members tr').each(function() {
                         $(this).show();
                    });
                }
            });
            $("#collection").change(function() {
                if(this.checked) {
                    $('#members tbody tr').each(function() {
                    	console.log($(':nth-child(6)', this).text());
                        if($(':nth-child(6)', this).text() == ' Yes ')
                        	$(this).hide();
                    });
                }
                else{
                    $('#members tr').each(function() {
                         $(this).show();
                    });
                }
            });

        });
        function addMember(data)
        {
            $('#members').prepend('<tr> <td>' + data.firstname + '</td> <td>' + data.lastname + '</td><td></td><td></td><td></td>No<td>  <button data-id="'+ data.id +'" data-toggle="modal" data-target="#member-order"><i class="fa fa-plus fa-fw fa-lg"></i></button><button data-id="' + data.id + '" data-toggle="modal" data-target="#member-edit"><i class="fa fa-edit fa-fw">  </i></button></td></tr>');
            $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
        }


        $('#member-order').on('show.bs.modal', function (event) {
          $('#memberordermodalcontent').load('{{ url('member/') }}/'+ $(event.relatedTarget).data('id'))
        });

        $('#member-edit').on('show.bs.modal', function (event) {
          $('#membereditmodalcontent').load('{{ url('member/') }}/'+ $(event.relatedTarget).data('id') + '/edit')
        });
    </script>
@stop


@section('content')

    <form id="member-form" name="member-form" class="form-inline" action="{{ URL::to('member') }}" method="post" enctype="multipart/form-data">
        <input type="search" id="filter" name="name" placeholder="First Name" class="form-control" autofocus="" autocomplete="off">
        <input type="search" id="lastname" name="lastname" placeholder="Last Name" class="form-control" autofocus="" autocomplete="off">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="submit" class="btn btn-outline btn-primary" data-ajax-submit="#member-form" data-ajax-callback-function="addMember"><i class="fa fa-plus fa-fw">  </i>Add Member</button>
    </form>
   @if(\Sentinel::check() && \Sentinel::inRole('admin'))
        Filter Bankinfo: <input type="checkbox" id="bankinfo"> <br />
        Filter Had Collection: <input type="checkbox" id="collection">
    @endif

    <div class="row">&nbsp;</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="members">

            <thead>
                <tr>

                    <th>First Name</th>
                    <th>Last Name</th>
                    <th class="col-sm-1">Actions</th>
                   @if(\Sentinel::check() && \Sentinel::inRole('admin'))
                        <th>BIC</th>
                        <th>Iban</th>
                        <th class="col-sm-1">Had Collection</th>
                    @endif

                </tr>
            </thead>

            <tbody>
                @foreach ($members as $m)
                <tr>
                    <td>{{ $m->firstname }}</td>
                    <td>{{ $m->lastname }}</td>
                    <td>
                        <button data-id="{{ $m->id }}" data-toggle="modal" data-target="#member-order"><i class="fa fa-plus fa-fw fa-lg"></i></button>
                        <button data-id="{{ $m->id }}"  data-toggle="modal" data-target="#member-edit"><i class="fa fa-edit fa-fw">  </i></button>
                    </td>
                    @if(\Sentinel::check() && \Sentinel::inRole('admin'))
                        <td>{{ $m->bic }}</td>
                        <td>{{ $m->iban }}</td>
                        <td>@if($m->had_collection) Yes @else No @endif</td>
                    @endif

                </tr>
                @endforeach
            </tbody>

        </table>
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