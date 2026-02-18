

<?php $__env->startSection('title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-bold m-0">User Management</h1>
            <div class="text-muted">Manage access users for this web</div>
        </div>

        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary">
            + Add User
        </a>
    </div>

    <div class="mt-3 mb-3 h-px bg-slate-300"></div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="GET" class="d-flex gap-2 mb-3">
        <input type="text" name="q" value="<?php echo e($q ?? ''); ?>" class="form-control" placeholder="Search name/login/posisi/role...">
        <select name="per_page" class="form-select" style="max-width:120px;">
            <?php $__currentLoopData = [10,15,25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($n); ?>" <?php if((int)request('per_page', 15) === $n): echo 'selected'; endif; ?>><?php echo e($n); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn-outline-primary">Search</button>
        <?php if(request()->query()): ?>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>LoginID</th>
                    <th>Nama</th>
                    <th>Inisial</th>
                    <th>PosisiID</th>
                    <th>Versi</th>
                    <th>Role</th>
                    <th>SU</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($u->UserID); ?></td>
                        <td><?php echo e($u->LoginID_plain); ?></td>
                        <td><?php echo e($u->Nama); ?></td>
                        <td><?php echo e($u->Inisial); ?></td>
                        <td><?php echo e($u->PosisiID); ?></td>
                        <td><?php echo e($u->Versi); ?></td>
                        <td>
                            <span class="badge text-bg-dark text-uppercase"><?php echo e($u->Role); ?></span>
                        </td>
                        <td>
                            <?php if((int)$u->Su === 1): ?>
                                <span class="badge text-bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?php echo e(route('admin.users.edit', $u->UserID)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>

                            <form action="<?php echo e(route('admin.users.destroy', $u->UserID)); ?>" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this user?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing <?php echo e($users->firstItem() ?? 0); ?> - <?php echo e($users->lastItem() ?? 0); ?>

        </div>
        <div>
            <?php echo e($users->links()); ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/admin/users/index.blade.php ENDPATH**/ ?>