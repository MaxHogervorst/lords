<?php $__env->startSection('script'); ?>
<script>
    var prices;
    $(document).ready(function() {

        $('#invoiceprice').blur(function()
             {
                 if( $(this).val() )
                 {
                    var id = $(this).val();
                     $('#productTotalPrice').prop('disabled', true);
                     $('#productPricePerPerson').prop('disabled', false);
                     $('#productPricePerPerson').val($('#invoiceprice option:selected').text());
                     $('#isupdate').val($(this).val());

                     $.each(prices, function(i, item){
                        if(id == item.id)
                        {
                            $('#productdescription').html(item.description);
                        }
                     });
                 }
                 else
                 {
                     $('#productTotalPrice').prop('disabled', false);
                     $('#productPricePerPerson').prop('disabled', false);
                     $('#productPricePerPerson').val('');
                     $('#productdescription').empty();
                     $('#isupdate').val('');
                 }
             });
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

                    $('#finalproductname').val($('#invoiceproduct option:selected').text());
                    $('#finalproductdescription').html($('#productdescription').val());
                    $('#finalselectedmembers').val($('.checkbox:checked:enabled').length);
                    calcutatePrice();

                } else {
                    $('#rootwizard').find('.pager .next').show();
                    $('#rootwizard').find('.pager .finish').hide();
                }

                if(index == 1)
                {
                    $.ajax({
                      method: 'get',
                      url: '<?php echo e(url('fiscus/invoiceprices')); ?>/' + $('#invoiceproduct').val(),
                      success: function(data){
                        $('#invoiceprice').empty();
                        $('#invoiceprice').append('<option> </option>');
                        prices = data;
                        $.each(data, function(i, item){
                            $('#invoiceprice').append('<option value="'+ item.id +'">'+ item.price +'</option>');
                        });
                        $('#productTotalPrice').val('').enable();
                        $('#productPricePerPerson').val('').enable();
                        $('#productdescription').empty().enable();
                      }
                    })

                }
                else if(index == 2 )
                {
                    $('.checkbox').each(function() { //loop through each checkbox
                        this.checked = false; //deselect all checkboxes with class "checkbox1"
                        this.disabled = false;
                    });
                    $.ajax({
                         method: 'get',
                         url: '<?php echo e(url('fiscus/allinvoicelines')); ?>/' + $('#invoiceproduct').val(),
                         success: function(data){
                           $.each(data, function(i, item){
                                $("#"+item.member_id).prop("checked", true);
                                $("#"+item.member_id).attr("disabled", true);

                           });
                         }
                       });
                    if($('#invoiceprice').val())
                    {
                        $.ajax({
                             method: 'get',
                             url: '<?php echo e(url('fiscus/specificinvoicelines')); ?>/' + $('#invoiceprice').val(),
                             success: function(data){
                                $.each(data, function(i, item){
                                       $("#"+item.member_id).prop("checked", true);
                                       $("#"+item.member_id).attr("disabled", false);
                                  });
                             }
                           })
                    }
                }
            },
             onTabClick: function(tab, navigation, index) {
                    return false;
                }
        });

        $('#rootwizard .finish').click(function() {

            $.ajax({
                url: '<?php echo e(url('fiscus/update')); ?>/'+ $('#invoiceproduct').val(),
                method: 'PUT=',
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div id="rootwizard">
    	<div class="navbar">
    	  <div class="navbar-inner">
    	    <div class="container">
                <ul>
                    <li><a href="#tab1" data-toggle="tab">Select Product</a></li>
                    <li><a href="#tab2" data-toggle="tab">Add/Edit Price</a></li>
                    <li><a href="#tab3" data-toggle="tab">Select Members</a></li>
                    <li><a href="#tab4" data-toggle="tab">Summary</a></li>
                </ul>
             </div>
          </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane" id="tab1">
                <form role="form" id="invoiceproductform" action="fiscus" method="post">
                    <div class="form-group">
                        <label for="inputEmail" class="control-label">Product Name</label>
                        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                        <select id="invoiceproduct" class="form-control" name="product_id">
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <a href="#" class="btn btn-danger" id="deleteProduct"  data-ajax-type="DELETE" data-ajax-submit="#invoiceproductform" data-ajax-callback-function="afterRefreshMessage" >Delete Product</a>
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="tab2">
                <form role="form" >
                    <div class="form-group has-feedback-left">
                        <label for="inputEmail" class="control-label">Change Price</label>
                        <select id="invoiceprice" class="form-control">
                            <option> </option>
                        </select>

                    </div>
                    <div class="form-group has-feedback-left">
                        <label for="inputEmail" class="control-label">New Total Price</label>
                            <input type="text" id="productTotalPrice" name="productTotalPrice" class="form-control"  autocomplete="off">
                            <i class="form-control-feedback glyphicon glyphicon-euro"></i>

                    </div>
                     <div class="form-group">
                        <label for="inputEmail" class="control-label">New Price per person</label>

                             <input type="text" id="productPricePerPerson" name="productPricePerPerson" class="form-control"  autocomplete="off">

                    </div>
                    <div class="form-group">
                        <label for="inputEmail" class="control-label">Description</label>
                              <textarea id="productdescription" name="productdescription" class="form-control" rows="3" ></textarea>

                    </div>

                </form>
            </div>
            <div class="tab-pane" id="tab3">
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
                                    <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td width="5%" align="center"><input type="checkbox" class="checkbox" name="member[]" value="<?php echo e($m->id); ?>" id="<?php echo e($m->id); ?>" > </td>
                                        <td><?php echo e($m->firstname); ?></td>
                                        <td><?php echo e($m->lastname); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>

                            </table>

                    </div>
                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                </form>
            </div>
            <div class="tab-pane" id="tab4">
                <form class="form-horizontal" id="finalproductform">
                    <input type="hidden" id="isupdate" name="isupdate" >
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
                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                </form>
            </div>

            <ul class="pager wizard">
                <li class="previous"><a href="#">Previous</a></li>
                <li class="next"><a href="#">Next</a></li>
                <li class="next finish" style="display:none;"><a href="javascript:;">Finish</a></li>
            </ul>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/fiscus/edit.blade.php ENDPATH**/ ?>