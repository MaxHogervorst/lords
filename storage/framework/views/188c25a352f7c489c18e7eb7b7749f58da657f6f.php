<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function() {
            $('#productprice').keypress(function(event) { return isNumber(event, true); });
        });
        $('#product-edit').on('show.bs.modal', function (event) {
                      $('#producteditmodalcontent').load('<?php echo e(url('product')); ?>/'+ $(event.relatedTarget).data('id') + '/edit')
                    });
        function addProduct(data)
        {
            $('#products').prepend('<tr> <td>' + data.name + '</td> <td>' + data.price + '</td> <td><button data-id="' + data.id +'" data-toggle="modal" data-target="#product-edit"><i class="fa fa-edit fa-fw">  </i></button></td> </tr>');
            $('tbody tr').removeClass('visible').show().addClass('visible').css({display: 'table-row'});
        }
    </script>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <form id="product-form" name="member-form" class="form-inline" action="<?php echo e(url('product')); ?>" method="post">
        <input type="search" id="filter" name="name" placeholder="Search or Add" class="form-control" autofocus="" autocomplete="off">
        <input type="search" id="productprice" name="productPrice" placeholder="Product price" class="form-control" autocomplete="off">
        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
        <button type="submit" class="btn btn-outline btn-primary" data-ajax-type="POST" data-ajax-submit="#product-form" data-ajax-callback-function="addProduct"><i class="fa fa-plus fa-fw">  </i>Add Product</button>
    </form>

    <div class="row">&nbsp;</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="products">

            <thead>
                <tr>

                    <th>Name</th>
                    <th>Price</th>
                    <th class="col-sm-1">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr id="<?php echo e($m->id); ?>">
                    <td><?php echo e($m->name); ?></td>
                    <td><?php echo e($m->price); ?></td>

                    <td>
                        <button data-id="<?php echo e($m->id); ?>" data-toggle="modal" data-target="#product-edit"><i class="fa fa-edit fa-fw">  </i></button>
                    </td>

                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>

        </table>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('modal'); ?>

<div id="product-edit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" >
 		<div class="modal-content">
            <div id="producteditmodalcontent"></div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/product/index.blade.php ENDPATH**/ ?>