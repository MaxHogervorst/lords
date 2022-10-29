<?php $__env->startSection('content'); ?>
    <form id="product-form" name="member-form" class="form-inline" action="<?php echo e(url('product')); ?>" method="post">
        <input type="search" id="filter" name="name" placeholder="Search or Add" class="form-control" autofocus="" autocomplete="off">
        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
    </form>

    <div class="row">&nbsp;</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="products">

            <thead>
            <tr>

                <th>Name</th>
                
                <th class="col-sm-1">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php $__currentLoopData = $invoice_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr id="<?php echo e($m->id); ?>">
                    <td><?php echo e($m->name); ?></td>
                    

                    <td>
                        <button data-id="<?php echo e($m->id); ?>" data-toggle="modal" data-target="#product-edit"><i class="fa fa-edit fa-fw">  </i></button>
                    </td>

                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>

        </table>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/fiscus/index.blade.php ENDPATH**/ ?>