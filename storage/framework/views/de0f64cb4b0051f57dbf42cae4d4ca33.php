

<?php $__env->startSection('title', $title ?? 'Assembly'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0"><?php echo e($title ?? 'Assembly'); ?></h1>
            <div class="text-slate-600">
                Detail page for: <span class="font-semibold"><?php echo e($subtitle ?? ''); ?></span>
            </div>
        </div>

        <a href="<?php echo e(route('home')); ?>"
           class="text-decoration-none text-light px-4 py-2 bg-red-600 transition delay-150 duration-300 ease-in-out hover:scale-110 hover:bg-red-700 rounded-pill">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="mt-3 h-px bg-slate-300"></div>

    <div class="mt-8 rounded-3xl overflow-hidden shadow-sm bg-[#0b0f6a] px-6 pt-4 pb-3">
        <div class="d-flex gap-2 mb-3">
            <a href="<?php echo e(route('assembly', ['tab' => 'lasting'] + request()->except('page'))); ?>"
               class="px-3 py-2 rounded-pill text-decoration-none fw
                    <?php echo e($activeTab === 'lasting' ? 'bg-[#33df89] text-dark fw-semibold' : 'bg-light text-dark border'); ?>">
                Lasting
            </a>

            <a href="<?php echo e(route('assembly', ['tab' => 'injection'] + request()->except('page'))); ?>"
               class="px-3 py-2 rounded-pill text-decoration-none
                    <?php echo e($activeTab === 'injection' ? 'bg-[#33df89] text-dark fw-semibold' : 'bg-light text-dark border'); ?>">
                Injection
            </a>
        </div>

        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">
            <form id="filterForm" method="GET" action="<?php echo e(route('assembly', ['tab' => $activeTab])); ?>" class="flex flex-wrap gap-3 items-center mb-4">

                
                <input type="hidden" name="year" value="<?php echo e(request('year', date('Y'))); ?>">

                
                <input type="hidden" name="q" value="">

                
                <input type="hidden" name="tab" value="<?php echo e($activeTab); ?>">

                
                <input
                    type="number"
                    name="year"
                    min="2000"
                    max="2100"
                    value="<?php echo e(request('year', date('Y'))); ?>"
                    class="px-3 py-2 rounded-lg border"
                    placeholder="Year"
                />
                <select name="per_page" class="px-3 py-2 rounded-lg border">
                    <?php $__currentLoopData = [10,15,25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($n); ?>" <?php if((int)request('per_page', 15) === $n): echo 'selected'; endif; ?>><?php echo e($n); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <button type="submit" class="px-4 py-2 btn btn-primary rounded-lg text-white font-semibold">
                    <i class="bi bi-search"></i> Apply
                </button>

                <?php if(request()->query()): ?>
                    <a href="<?php echo e(route('assembly', ['tab' => $activeTab])); ?>?year=<?php echo e(request('year', date('Y'))); ?>" class="px-4 py-2 rounded-lg border">
                        Clear
                    </a>
                <?php endif; ?>

                <div class="w-100"></div>

                
                <?php echo $__env->make('pages.partials._process_table', [
                    'slug' => $slug,
                    'columns' => $columns,
                    'rows' => $rows,
                    'isAssembly' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            </form>
        </div>
    </div>

    <script>
        // Optional: auto-submit filter on Enter only
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/pages/assembly.blade.php ENDPATH**/ ?>