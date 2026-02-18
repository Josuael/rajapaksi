<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-slate-50 text-slate-900">

<?php
    // kalau sedang di /process/{slug}, ini akan berisi 'stitches', 'strobel', dst
    $currentSlug = request()->route('slug');
    $currentSlugForAssembly = request()->route('slug'); // injection / lasting
    $isAssemblyActive = in_array($currentSlugForAssembly, ['injection', 'lasting'], true);
?>

<?php
  // NOTE: ini bagian lama lu, gw biarin (walau redundant) biar gak ganggu hal lain
  $name = auth()->user()->name ?? 'MUser';
  $initials = collect(explode(' ', trim($name)))
      ->filter()
      ->map(fn($w) => strtoupper(substr($w, 0, 1)))
      ->take(2)
      ->implode('');
?>

<div class="app-shell">

    <aside class="sidebar" aria-label="Sidebar">
        <div class="sidebar__brand">
            <i class="bi bi-lightning-charge-fill text-white"></i>
            <span class="sidebar__text">PT. Rajapakasi</span>
        </div>

        <nav class="sidebar__nav">

            
            <a class="sidebar__link <?php echo e(request()->routeIs('home') || request()->routeIs('dashboard') ? 'is-active' : ''); ?>"
               href="<?php echo e(route('home')); ?>">
                <i class="bi bi-house-door"></i>
                <span class="sidebar__text">Home Page</span>
            </a>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-process')): ?>
                
                <a class="sidebar__link <?php echo e($currentSlug === 'stitches' ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('process.show', 'stitches')); ?>">
                    <i class="bi bi-scissors"></i>
                    <span class="sidebar__text">Stitches</span>
                </a>

                
                <a class="sidebar__link <?php echo e($currentSlug === 'strobel' ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('process.show', 'strobel')); ?>">
                    <i class="bi bi-gear"></i>
                    <span class="sidebar__text">Strobel</span>
                </a>

                
                <a class="sidebar__link <?php echo e(request()->routeIs('assembly') ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('assembly', ['tab' => 'injection'])); ?>">
                    <i class="bi bi-tools"></i>
                    <span class="sidebar__text">Assembly</span>
                </a>

                
                <a class="sidebar__link <?php echo e($currentSlug === 'finishing' ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('process.show', 'finishing')); ?>">
                    <i class="bi bi-check2-square"></i>
                    <span class="sidebar__text">Finishing</span>
                </a>

                
                <a class="sidebar__link <?php echo e($currentSlug === 'recap' ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('process.show', 'recap')); ?>">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="sidebar__text">Recap</span>
                </a>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-users')): ?>
                <a class="sidebar__link <?php echo e(request()->is('admin/users*') ? 'is-active' : ''); ?>"
                   href="<?php echo e(route('admin.users.index')); ?>">
                    <i class="bi bi-people"></i>
                    <span class="sidebar__text">Users</span>
                </a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">

        
        <div class="w-100 d-flex justify-content-end align-items-center gap-3 p-3">
            <?php
                // ✅ Ambil dari M_User (auth user lu)
                $u = auth()->user();

                // Nama tampil (M_User.Nama) fallback aman
                $displayName = $u?->Nama
                    ?? $u?->name
                    ?? $u?->LoginID_plain
                    ?? 'User';

                // Role web (M_User.Role)
                $displayRole = strtoupper((string)($u?->Role ?? $u?->role ?? 'USER'));
                if ($displayRole === '') $displayRole = 'USER';

                // Inisial: prioritas M_User.Inisial, fallback dari nama (2 huruf)
                $inisialRaw = trim((string)($u?->Inisial ?? ''));
                if ($inisialRaw !== '') {
                    $displayInitials = strtoupper(substr($inisialRaw, 0, 2));
                } else {
                    $displayInitials = collect(explode(' ', trim($displayName)))
                        ->filter()
                        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                        ->take(2)
                        ->implode('');
                    if ($displayInitials === '') $displayInitials = 'U';
                }
            ?>

            
            <div>
                <a href="<?php echo e(route('home')); ?>" class="d-flex align-items-center text-decoration-none"
                    data-bs-toggle="tooltip"
                    data-bs-title="PT. Rajapakasi">
                    <img
                        src="<?php echo e(asset('images/logo-pt-rap.jpeg')); ?>"
                        alt="PT. Rajapakasi"
                        class="bg-white border rounded-pill transition delay-150 duration-300 ease-in-out hover:scale-105 hover:shadow-lg"
                        style="height:52px;width:auto;"
                    >
                </a>
            </div>

            <div>
                <a href="<?php echo e(route('home')); ?>" class="d-flex align-items-center text-decoration-none"
                    data-bs-toggle="tooltip"
                    data-bs-title="PT. Weba Footwear">
                    <img
                        src="<?php echo e(asset('images/logo-weba.png')); ?>"
                        alt="PT. Weba Footwear"
                        class="bg-white border rounded-pill transition delay-150 duration-300 ease-in-out hover:scale-105 hover:shadow-lg"
                        style="height:52px;width:auto;"
                    >
                </a>
            </div>


            <div class="position-relative" id="profileMenu">
                <button type="button" id="profileBtn"
                    class="d-flex align-items-center gap-2 rounded-pill px-3 py-2 border bg-white transition delay-150 duration-300 ease-in-out hover:scale-105 hover:shadow-lg">
                    <div class="rounded-circle bg-blue-900 text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;">
                        <?php echo e($displayInitials); ?>

                    </div>
                    <span class="d-none d-md-block fw-semibold text-dark">
                        <?php echo e($displayName); ?>

                    </span>
                </button>

                <div id="profileDropdown"
                    class="d-none position-absolute end-0 mt-2 bg-white border rounded-3 shadow"
                    style="width: 240px; z-index: 9999;">
                    <div class="px-3 py-2 border-bottom">
                        <div class="small text-muted">Signed in as</div>
                        <div class="fw-semibold"><?php echo e($displayName); ?></div>
                        <div class="small text-muted"><?php echo e($displayRole); ?></div>
                    </div>

                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="w-100 text-start px-3 py-2 border-0 bg-transparent text-danger fw-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php echo $__env->yieldContent('content'); ?>
    </main>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(session('toast')): ?>
  <script>window.__toast = json(session('toast'));</script>
<?php endif; ?>


</body>
</html>
<?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/layouts/layout.blade.php ENDPATH**/ ?>