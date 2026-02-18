

<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0"><?php echo e($title); ?></h1>
            <div class="text-slate-600">
                Detail page for: <span class="font-semibold"><?php echo e($slug); ?></span>
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
        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">

            <form method="GET" id="filterForm" class="flex flex-wrap gap-3 items-center mb-4">
                <input type="hidden" name="year" value="<?php echo e(request('year', date('Y'))); ?>">
                <input type="hidden" name="q" value="">

                <?php
                    $hasProcessCol = $hasProcessCol ?? false;
                    $processOptions = $processOptions ?? collect();
                    $processUsable = (!empty($hasProcessCol) && $processOptions->count());
                ?>

                <?php if(($slug ?? '') === 'recap'): ?>
                    <div class="w-100"></div>

                    
                    <div class="row g-3 align-items-end w-100 mb-2">

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Date From</label>
                            <input type="date"
                                   name="tanggal_from"
                                   class="form-control"
                                   value="<?php echo e(request('tanggal_from')); ?>">
                            <div class="form-text">(Filter tanggal dari)</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Date To</label>
                            <input type="date"
                                   name="tanggal_to"
                                   class="form-control"
                                   value="<?php echo e(request('tanggal_to')); ?>">
                                   <div class="form-text">(Filter tanggal sampai)</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Process</label>
                            <select name="process" class="form-select" <?php echo e($processUsable ? '' : 'disabled'); ?>>
                                <option value="">All Process</option>
                                <?php if($processUsable): ?>
                                    <?php $__currentLoopData = $processOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($p); ?>" <?php echo e(request('process') == $p ? 'selected' : ''); ?>>
                                            <?php echo e($p); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">
                                <?php if($processUsable): ?>
                                    (Pilih process untuk filter data recap)
                                <?php else: ?>
                                    (Process filter tidak tersedia)
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-3">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>

                                <a href="<?php echo e(url()->current()); ?>?year=<?php echo e(request('year', date('Y'))); ?>&per_page=<?php echo e(request('per_page', 15)); ?>"
                                   class="btn btn-outline-secondary px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>

                    </div>

                    
                    <div class="w-100 d-flex flex-wrap gap-2 align-items-center">
                        <select name="per_page" class="px-3 py-2 rounded-lg border">
                            <?php $__currentLoopData = [10,15,25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($n); ?>" <?php if((int)request('per_page', 15) === $n): echo 'selected'; endif; ?>><?php echo e($n); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <?php if(request()->query()): ?>
                            <a href="<?php echo e(url()->current()); ?>?year=<?php echo e(request('year', date('Y'))); ?>"
                               class="px-4 py-2 rounded-lg border">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
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
                        <i class="bi bi-search"></i>
                        Apply
                    </button>

                    <?php if(request()->query()): ?>
                        <a href="<?php echo e(url()->current()); ?>?year=<?php echo e(request('year', date('Y'))); ?>" class="px-4 py-2 rounded-lg border">
                            Clear
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="w-100"></div>

                
                <?php echo $__env->make('pages.partials._process_table', [
                    'slug' => $slug,
                    'columns' => $columns,
                    'rows' => $rows,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/pages/process-detail.blade.php ENDPATH**/ ?>