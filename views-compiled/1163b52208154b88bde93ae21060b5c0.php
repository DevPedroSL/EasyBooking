<?php $__env->startSection('title', 'Inicio - EasyBooking'); ?>

<?php $__env->startSection('content'); ?>

<h1>Inicio</h1>
<?php $__currentLoopData = $barbershops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barbershop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php echo e($barbershop->name); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/inicio.blade.php ENDPATH**/ ?>