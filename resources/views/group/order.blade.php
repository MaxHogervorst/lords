<script>
    $(document).ready(function(){
        $('#product-select').selectize();
        $('#member-select').selectize({
            onType: function(str){
                var trimmedInput;
                trimmedInput=	$.trim($(str).val()).toLowerCase(); //trim white space
                //query = query.replace(/ /gi, '|'); //add OR for regex query
                $('table#groupmembers-table tbody tr').each(function() {
                    ($(this).text().search(new RegExp(trimmedInput, 'i')) < 0) ? $(this).hide().removeClass('visible') : $(this).show().addClass('visible');
                });
            }
        });
        $('#amount').bootstrapNumber();




    });
    $(document).on("click", '.btn-delete-group-member', function(){
                $.ajax({
                    url: '{{ url("group/deletegroupmember") }}/' + $('.btn-delete-group-member').attr('id'),
                    method: 'GET',
                    success: function(data)
                    {
                        if(data.errors != undefined)
                        {
                            new PNotify({
                                title: 'Error',
                                text: 'Groupmember could not be deleted',
                                type: 'custom',
                                addclass: 'notification-danger',
                                icon: 'fa fa-exclamation'
                            });
                        }
                        else
                        {
                            console.log('test');
                            var groupmembers = $('#groupmembers-table');
                            groupmembers.find('#'+ data.id).remove();

                            new PNotify({
                                title: 'Succes',
                                text: 'Groupmember successfully deleted',
                                type: 'custom',
                                addclass: 'notification-success',
                                icon: 'fa fa-exclamation'
                            });
                        }
                    }
                })
            })
    function addGroupMember(data)
    {
        $('#groupmembers-table').prepend('<tr id="' + data.id + '" ><td>' + data.membername  + '</td><td><a href="#" class="btn-delete-group-member" id="' + data.id + '"><i class="fa fa-trash-o fa-fw fa-lg"></i></a> </td></tr>');
    }
</script>
<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">{{ $group->name }}</h4>
			</div>
			<div class="modal-body">
			<div id="errorsModal"></div>
				<ul class="nav nav-tabs" role="tablist" id="grouptab">
				  <li role="presentation" class="active"><a href="#orders" role="tab" data-toggle="tab">Orders</a></li>
				  <li role="presentation"><a href="#groupmembers" role="tab" data-toggle="tab">Group Members</a></li>
				</ul>
				<div class="tab-content">
				  <div role="tabpanel" class="tab-pane active" id="orders">
				  		<div class="row">&nbsp;</div>
				  		<form id="order-form" class="form-inline" method="post" action="{{ url('order/store/Group') }}">
					        <div class="form-group">
								<input type="number" id="amount" name="amount" value="1"   class="form-control" autocomplete="off">
                                <select id="product-select" name="product" class="form-control">
                                    <option> Select Product</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
								<input type="Hidden" id="memberId" name="memberId"  value="{{ $group->id }}">
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
								@foreach($group->orders()->where('invoice_group_id', '=', $currentmonth->id)->get() as $o)
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
								@foreach($group->orders()->where('invoice_group_id', '=', $currentmonth->id)->selectRaw('orders.product_id, count(orders.id) as count')->groupby('product_id')->get() as $total)
                                    <tr>
                                        <td> {{ $total->product->name }}</td>
                                        <td> {{ $total->count }}</td>
                                    </tr>

                                @endforeach
						</table>
				  </div>
				  <div role="tabpanel" class="tab-pane" id="groupmembers">
				  			<div class="row">&nbsp;</div>
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
							<div class="row">&nbsp;</div>
							<div class="scroll-table">
								<table id="groupmembers-table" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th> Name </th>
											<th> Actions </th>
										</tr>
									</thead>
									@foreach($group->members as $m)
									    <tr id="{{ $m->pivot->id }}">
									        <td>{{ $m->firstname . ' ' . $m->lastname }}</td>
									        <td>
									            <a href="#" class="btn-delete-group-member" id="{{ $m->pivot->id }}"><i class="fa fa-trash-o fa-fw fa-lg"></i></a>
                                            </td>
									    </tr>
									@endforeach

								</table>
							</div>
				  </div>
				</div>

		</div>
		<div class="modal-footer">
                    <a href="#" id="editMember" class="btn-edit"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-edit fa-fw">  </i>Edit Group</button></a>
		</div>