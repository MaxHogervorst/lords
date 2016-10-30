@extends('layout.master')
@section('script')
<script>
    $(document).ready(function() {
        $('#rootwizard').bootstrapWizard({
            onTabShow: function(tab, navigation, index) {
                var $total = navigation.find('li').length;
                var $current = index+1;
                var $percent = ($current/$total) * 100;
                $('#rootwizard').find('.bar').css({width:$percent+'%'});

                // If it's the last tab then hide the last button and show the finish instead
                if($current >= $total) {
                    $('#rootwizard').find('.pager .next').hide();
                    $('#rootwizard').find('.pager .finish').show();
                    $('#rootwizard').find('.pager .finish').removeClass('disabled');

                    $('#finalproductname').val($('#productname').val());
                    $('#finalproductdescription').html($('#productdescription').val());
                    $('#finalselectedmembers').val($('.checkbox:checked').length);
                    calcutatePrice();

                } else {
                    $('#rootwizard').find('.pager .next').show();
                    $('#rootwizard').find('.pager .finish').hide();
                }
            },
            onTabClick: function(tab, navigation, index) {
            		return false;
            	}
        });

        $('#rootwizard .finish').click(function() {

            $.ajax({
                url: '{{ url('fiscus') }}',
                method: 'POST',
                data: $('#membersform, #finalproductform').serialize(),
                success: function(data){
                    if(data.success)
                    {
                        afterRefreshMessage(data);
                    }
                    else
                    {
                         $.each(data.errors, function (input_name) {
                                            new PNotify({
                                                           title: 'Missing field',
                                                           text: input_name + ' is empty',
                                                           addclass: 'notification-error',
                                                           icon: 'fa fa-exclamation'
                                                       });
                                        });
                    }

                }
            })
        });


    });



</script>
@stop
@section('content')
    <div id="rootwizard">
    	<div class="navbar">
    	  <div class="navbar-inner">
    	    <div class="container">
                <ul>
                    <li><a href="#tab1" data-toggle="tab">Add Product</a></li>
                    <li><a href="#tab2" data-toggle="tab">Select Members</a></li>
                    <li><a href="#tab3" data-toggle="tab">Summary</a></li>
                </ul>
             </div>
          </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane" id="tab1">
                <form role="form" >
                    <div class="form-group">
                        <label for="inputEmail" class="control-label">Product Name</label>
                                <input type="text" id="productname"  class="form-control" autocomplete="off">

                    </div>
                    <div class="form-group has-feedback-left">
                        <label for="inputEmail" class="control-label">Total Price</label>
                            <input type="text" id="productTotalPrice" name="productTotalPrice" class="form-control"  autocomplete="off">
                            <i class="form-control-feedback glyphicon glyphicon-euro"></i>


                    </div>
                     <div class="form-group">
                        <label for="inputEmail" class="control-label">Price per person</label>

                             <input type="text" id="productPricePerPerson" name="productPricePerPerson" class="form-control"  autocomplete="off">

                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="control-label">Description</label>

                              <textarea id="productdescription" name="productdescription" class="form-control" rows="3" ></textarea>

                    </div>
                </form>
            </div>
            <div class="tab-pane" id="tab2">
                <form class="form-horizontal" id="membersform">
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Search Member</label>
                        <div class="col-sm-9">

                            <input type="text" id="filter" name="name"  class="form-control" autofocus="" autocomplete="off">
                            <div class="row">&nbsp;</div>
                            <a href="#" id="selecctall"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-check fa-fw">  </i>Select All</button></a>
                            <a href="#" id="deselecctall"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-check fa-fw">  </i>Deselect All</button></a>
                        </div>
                    </div>
                    <div class="table-responsive">
                       <div class="row">&nbsp;</div>
                            <table class="table table-bordered table-striped" id="members">

                                <thead>
                                    <tr>

                                        <th align="center">Action</th>

                                        <th>First Name</th>
                                        <th>Last Name</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($members as $m)
                                    <tr>
                                        <td width="5%" align="center"><input type="checkbox" class="checkbox" name="member[]" value="{{ $m->id }}" > </td>
                                        <td>{{ $m->firstname }}</td>
                                        <td>{{ $m->lastname }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>

                            </table>

                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
            <div class="tab-pane" id="tab3">
                <form class="form-horizontal" id="finalproductform">
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Product Name</label>
                        <div class="col-sm-9">
                            <input type="text" id="finalproductname" name="finalproductname" readonly  class="form-control" autofocus="" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea id="finalproductdescription" name="finalproductdescription" readonly class="form-control" rows="3" ></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Total Price</label>
                        <div class="col-sm-9">
                            <input type="text" id="finaltotalprice" name="finaltotalprice" readonly  class="form-control" autofocus="" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Price Per Person</label>
                        <div class="col-sm-9">
                            <input type="text" id="finalpriceperperson" name="finalpriceperperson" readonly  class="form-control" autofocus="" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-1 control-label">Total Selected Members</label>
                        <div class="col-sm-9">
                            <input type="text" id="finalselectedmembers" name="finalselectedmembers" readonly  class="form-control" autofocus="" autocomplete="off">
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>

            <ul class="pager wizard">
                <li class="previous"><a href="#">Previous</a></li>
                <li class="next"><a href="#">Next</a></li>
                <li class="next finish" style="display:none;"><a href="javascript:;">Finish</a></li>
            </ul>
        </div>
    </div>
@stop