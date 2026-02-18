<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <?php echo e($slot); ?>


    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if(session('toast')): ?>
    <script>window.__toast = json(session('toast'));</script>
    <?php endif; ?>

</body>
</html>
<?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/layouts/guest.blade.php ENDPATH**/ ?>