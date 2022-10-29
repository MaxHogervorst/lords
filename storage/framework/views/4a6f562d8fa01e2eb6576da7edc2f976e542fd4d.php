<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function(){
            $('#invoiceGroup').selectize({
                selectOnTab: true,
                dropdownParent: 'body'
            });

        $('#invoiceMonth').datepicker( {
                        format: "MM-yyyy",
                        viewMode: "months",
                        minViewMode: "months",
                        autoclose: true
                    });

        $('#newinvoicegroupbutton').click(function() {
            $("#newInvoiceGroupModal").modal('show');
            return false;
        })

        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <form id="invoicegroupForm" method="post" action="invoice/selectinvoicegroup">
    <div class="row">
        <label for="inputEmail" class="col-lg-1 control-label">Select Month</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                <select id="invoiceGroup" name="invoiceGroup" class="form-control"  autocomplete="off">
                        <option value="">Search and select month/option>
                        <?php $__currentLoopData = $invoicegroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($i->status): ?>
                                <option value=<?php echo e($i->id); ?>>Active Month: <?php echo e($i->name); ?></option>
                            <?php else: ?>
                                <option value=<?php echo e($i->id); ?>><?php echo e($i->name); ?></option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#invoicegroupForm" data-ajax-callback-function="reload"><i class="fa fa-check fa-fw">  </i>Select </button>
                    <button id="newinvoicegroupbutton" class="btn btn-outline btn-primary" ><i class="fa fa-plus fa-fw">  </i>New</button>




                </span>
           </div>
        </div>
    </div>
    </form>

    <div class="row">&nbsp;</div>
    <a href="invoice/excel" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-excel-o fa-fw">  </i>Export to Excel</button></a>
    <a href="invoice/pdf" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-pdf-o fa-fw">  </i>Export to PDF</button></a>
    <a href="invoice/sepa" target="_blank"><button type="button" class="btn btn-outline btn-primary"><i class="fa fa-file-pdf-o fa-fw">  </i>Export to SEPA</button></a>
    <div class="row">&nbsp;</div>

     <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="4"><h3><b><?php echo e($m->firstname . ' ' . $m->lastname); ?></b></h3></th>
                </tr>
                <tr>
                    <th>Product</th>
                    <th>Description</th>
                    <th class="col-sm-1">Amount</th>
                    <th class="col-sm-1">TotalPrice</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php $__currentLoopData = $m->orders()->where('invoice_group_id', '=', $currentmonth->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $price = $o->amount * $products[$o->product_id]['price']; $total += $price; ?>
                    <tr>
                        <td> <?php echo e($products[$o->product_id]['name']); ?></td>
                        <td> <?php echo e($products[$o->product_id]['name']); ?></td>
                        <td> <?php echo e($o->amount); ?></td>
                        <td>&euro;<?php echo e(sprintf('%.2f', $price)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $m->groups()->where('invoice_group_id', '=', $currentmonth->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $totalprice = 0; ?>
                    <?php $__currentLoopData = $g->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $totalprice += $o->amount * $products[$o->product_id]['price']; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $totalmebers = $g->members->count(); $price = ($totalprice / $totalmebers); $total += $price; ?>

                    <tr>
                        <td><?php echo e($g->name); ?></td>
                        <td>Groupmembers: <?php echo e($totalmebers); ?> Total price: &euro;<?php echo e($totalprice); ?></td>
                        <td>1</td>
                        <td>&euro;<?php echo e(sprintf('%.2f', $price)); ?></td>
                    </tr>


                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $m->invoice_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $il): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($il->productprice->product->invoice_group_id == $currentmonth->id): ?>
                        <?php $price = $il->productprice->price; $total += $price; ?>
                        <tr>
                            <td><?php echo e($il->productprice->product->name); ?></td>
                            <td><?php echo e($il->productprice->description); ?></td>
                            <td>1</td>
                            <td>&euro;<?php echo e(sprintf('%.2f', $price)); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </tbody>
            <tfoot>
                <tr class="info">
                    <td colspan="3" align="right"><b>Total:</b></td>
                    <td align="left"><b>&euro;<?php echo e(sprintf('%.2f', $total)); ?></b></td>
                </tr>
            </tfoot>


        </table>
        <div class="row">&nbsp;</div>
        <div class="row">&nbsp;</div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>

    <div id="newInvoiceGroupModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog" >
     		<div class="modal-content">
    			<div class="modal-header">
    				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    				<h4 class="modal-title">New Month</h4>
    			</div>
    			<div class="modal-body">
    				<form id="NewInvoiceGroupForm"  class="form-inline" action="invoice/storeinvoicegroup" method="post">
    			        <div class="form-group">
    			            Select Month and year:
    						<input type="text" id="invoiceMonth" name="invoiceMonth" class="form-control" value="" >
    						<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
    						<button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#NewInvoiceGroupForm" data-ajax-callback-function="reload"><i class="fa fa-save fa-fw">  </i>New Month</button>

    					</div>
    				</form>
    			</div>
    		</div><!-- /.modal-content -->
    	</div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/invoice/index.blade.php ENDPATH**/ ?>