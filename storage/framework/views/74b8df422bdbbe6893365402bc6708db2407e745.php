<?php if(Session::has('success')): ?>
    <div class="hidden" data-notification="success" data-type="success">
        <h4>Succes!</h4>
        <div class="message"><?php echo e(Session::get('success')); ?></div>
    </div>
<?php endif; ?>

<?php if(Session::has('danger')): ?>
    <div class="hidden" data-notification="danger" data-type="danger">
        <h4>Error!</h4>
        <div class="message"><?php echo e(Session::get('danger')); ?></div
    </div>
<?php endif; ?>

<?php if(Session::has('warning')): ?>
    <div class="hidden" data-notification="warning" data-type="warning">
        <h4>Warning!</h4>
        <div class="message"><?php echo e(Session::get('warning')); ?></div
    </div>
<?php endif; ?>

<?php if(Session::has('info')): ?>
    <div class="hidden" data-notification="info" data-type="info">
        <h4>Let op!</h4>
        <div class="message"><?php echo e(Session::get('info')); ?></div
    </div>
<?php endif; ?>

<?php if(Session::has('error')): ?>
    <div class="hidden" data-notification="error" data-type="error">
        <h4>Let op!</h4>
        <div class="message"><?php echo e(Session::get('error')); ?></div
    </div>
<?php endif; ?>

<?php if(count($errors) > 0): ?>
    <div class="hidden" data-notification="danger" data-type="danger">
        <h4>Form errors</h4>
        <div class="message">
        <?php $__currentLoopData = $errors->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div data-error-field="<?php echo e($field); ?>"><?php echo e($errors->first($field)); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?><?php /**PATH /var/www/html/resources/views/layout/notifications.blade.php ENDPATH**/ ?>