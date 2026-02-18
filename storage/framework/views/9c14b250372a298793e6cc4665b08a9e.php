

<?php $__env->startSection('title', 'Add User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">Add User</h1>
            <div class="text-muted">Create new web user</div>
        </div>
        <a href="<?php echo e(route('admin.users.index')); ?>"
           class="text-decoration-none text-light px-4 py-2 bg-red-600 transition delay-150 duration-300 ease-in-out hover:scale-110 hover:bg-red-700 rounded-pill">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="mt-3 mb-3 h-px bg-slate-300"></div>

    <div class="mt-8 rounded-3xl overflow-hidden shadow-sm bg-[#0b0f6a] px-6 pt-4 pb-3">
        <div class="mt-8 rounded-3xl bg-slate-100 shadow p-6">

            <form method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="row g-3">
                <?php echo csrf_field(); ?>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">LoginID</label>
                    <input type="text" name="login_name" class="form-control" value="<?php echo e(old('login_name')); ?>" required>
                    <?php $__errorArgs = ['login_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="form-text">Ini yang dipakai untuk login (username perusahaan).</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo e(old('nama')); ?>">
                    <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Inisial <span class="text-muted">(required by DB)</span></label>
                    <input type="text" name="inisial" class="form-control" value="<?php echo e(old('inisial')); ?>" maxlength="5" required>
                    <?php $__errorArgs = ['inisial'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">PosisiID <span class="text-muted">(required by DB)</span></label>
                    <select name="posisi_id" class="form-select" required>
                        <option value="" disabled <?php echo e(old('posisi_id') ? '' : 'selected'); ?>>-- Select Posisi --</option>
                        <?php $__currentLoopData = $posisiOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pos); ?>" <?php echo e(old('posisi_id') == $pos ? 'selected' : ''); ?>>
                                <?php echo e($pos); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['posisi_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role (akses web)</label>
                    <select name="role" class="form-select" required>
                        <?php $__currentLoopData = ['admin' => 'ADMIN', 'internal' => 'INTERNAL', 'karyawan' => 'KARYAWAN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k); ?>" <?php echo e(old('role','karyawan') == $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Versi <span class="text-muted">(required by DB)</span></label>
                    <input type="text" name="versi" class="form-control" value="<?php echo e(old('versi', $defaultVersi)); ?>" required>
                    <?php $__errorArgs = ['versi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="form-text">Default: <?php echo e($defaultVersi); ?></div>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="su" name="su" <?php echo e(old('su') ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="su">
                            Su (legacy superuser)
                        </label>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle"></i> Save
                    </button>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/admin/users/create.blade.php ENDPATH**/ ?>