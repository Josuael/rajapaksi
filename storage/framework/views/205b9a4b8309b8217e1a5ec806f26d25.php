

<?php $__env->startSection('title', 'Home Page'); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between">
        <h1 class="fw-bold m-0"">Home Page</h1>
    </div>

    <div class="mt-3 h-px bg-slate-300"></div>

    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-1 xl:grid-cols-2">
        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('process.show', $card['slug'])); ?>" class="home-card group">
                <div class="home-card__inner">
                    <div class="home-card__title"><?php echo e($card['title']); ?> </div>
                    <div class="home-card__header mt-4">
                        <div>
                            <div class="home-card__sub fw-medium">Today</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value"><?php echo e(number_format($card['today'], 0, ',', '.')); ?></div>
                    </div>

                    <div class="home-card__row is-green">
                        <div>
                            <div class="home-card__sub fw-medium">Finish</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value"><?php echo e(number_format($card['finish'], 0, ',', '.')); ?></div>
                    </div>

                    <div class="home-card__row is-yellow">
                        <div>
                            <div class="home-card__sub fw-medium">Balance</div>
                            <div class="home-card__sub2">Product</div>
                        </div>
                        <div class="home-card__value"><?php echo e(number_format($card['balance'], 0, ',', '.')); ?>

</div>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/pages/home.blade.php ENDPATH**/ ?>