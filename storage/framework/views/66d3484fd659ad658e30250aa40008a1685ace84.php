<?php $__env->startSection('content'); ?>
    <div class="col-lg-6">

        <div class="panel panel-default">
            <div class="panel-heading">
                Last Five Orders
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" >
                        <thead>
                            <tr>
                                <td> Date </td>
                                <td> Name </td>
                                <td> Amount </td>
                                <td> Product </td>
                            </tr>
                        </thead>
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($m->created_at); ?></td>
                            <td><?php echo e(isset($m->ownerable) ? isset($m->ownerable->name) ?  $m->ownerable->name : $m->ownerable->firstname . ' ' . $m->ownerable->lastname : 'verwijderd'); ?></td>
                            <td><?php echo e($m->amount); ?></td>
                            <td><?php echo e($m->product->name); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     </table>
                </div>
            </div>
        </div>

    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/home/index.blade.php ENDPATH**/ ?>