

<?php
    $currentSort = request('sort', '');
    $currentDir  = request('dir', 'asc');
?>

<div class="table-responsive w-100">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isActive = ($currentSort === $key);
                        $nextDir  = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';

                        $query = request()->query();
                        $query['sort'] = $key;
                        $query['dir']  = $nextDir;
                        $query['page'] = 1;

                        $href = url()->current() . '?' . http_build_query($query);
                    ?>

                    <th class="text-nowrap">
                        <a href="<?php echo e($href); ?>"
                           class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                            <span><?php echo e($label); ?></span>

                            <?php if($isActive): ?>
                                <?php if($currentDir === 'asc'): ?>
                                    <i class="bi bi-caret-up-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-caret-down-fill"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="bi bi-arrow-down-up text-muted"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>

            
            <tr>
                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th>
                        <input
                            type="text"
                            name="col[<?php echo e($key); ?>]"
                            value="<?php echo e(request('col.'.$key)); ?>"
                            class="form-control form-control-sm"
                            placeholder="Search <?php echo e($label); ?>"
                            list="dl-<?php echo e($key); ?>"
                            data-suggest-col="<?php echo e($key); ?>"
                            oninput="window.__webaSuggest?.handleInput(this)"
                            onchange="document.getElementById('filterForm')?.submit()"
                        />

                        
                        <datalist id="dl-<?php echo e($key); ?>"></datalist>
                    </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr data-row style="cursor:pointer;">
                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-nowrap">
                            <?php echo e(data_get($row, $key)); ?>

                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(count($columns)); ?>" class="text-center py-4 text-muted">
                        No data found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (! $__env->hasRenderedOnce('c089f3da-7dfc-482c-aee5-410aaa6ca8a7')): $__env->markAsRenderedOnce('c089f3da-7dfc-482c-aee5-410aaa6ca8a7'); ?>
    <script>
        // ✅ Lightweight autocomplete (no library)
        // - debounce 250ms
        // - fetch /process/{slug}/suggest?col=...&q=...
        // - fill datalist
        (function () {
            const debounceMs = 250;
            const timers = new Map();
            const inflight = new Map();

            function getSlug() {
                // url: /process/{slug} or /assembly/{tab}
                const path = window.location.pathname;
                const parts = path.split('/').filter(Boolean);

                // /process/stitches
                if (parts[0] === 'process' && parts[1]) return parts[1];

                // /assembly/injection
                if (parts[0] === 'assembly') return parts[1] || 'injection';

                return null;
            }

            async function fetchSuggest(slug, col, q) {
                if (!slug) return [];
                const url = `/process/${encodeURIComponent(slug)}/suggest?col=${encodeURIComponent(col)}&q=${encodeURIComponent(q)}&limit=10`;

                // abort previous
                const key = slug + '|' + col;
                if (inflight.has(key)) {
                    try { inflight.get(key).abort(); } catch (e) {}
                    inflight.delete(key);
                }

                const ctrl = new AbortController();
                inflight.set(key, ctrl);

                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' }, signal: ctrl.signal });
                    if (!res.ok) return [];
                    const data = await res.json();
                    if (!Array.isArray(data)) return [];
                    return data;
                } catch (e) {
                    return [];
                } finally {
                    inflight.delete(key);
                }
            }

            function fillDatalist(col, list) {
                const dl = document.getElementById(`dl-${col}`);
                if (!dl) return;
                dl.innerHTML = '';
                for (const v of list) {
                    const opt = document.createElement('option');
                    opt.value = v;
                    dl.appendChild(opt);
                }
            }

            window.__webaSuggest = {
                handleInput(inputEl) {
                    const col = inputEl?.dataset?.suggestCol;
                    if (!col) return;

                    const slug = getSlug();
                    const q = (inputEl.value || '').trim();

                    // jangan spam fetch kalau kosong
                    if (q.length < 2) {
                        fillDatalist(col, []);
                        return;
                    }

                    const tKey = slug + '|' + col;
                    if (timers.has(tKey)) clearTimeout(timers.get(tKey));

                    timers.set(tKey, setTimeout(async () => {
                        const list = await fetchSuggest(slug, col, q);
                        fillDatalist(col, list);
                    }, debounceMs));
                }
            };
        })();
    </script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-3 w-100">
    <div class="text-muted small">
        Showing <?php echo e($rows->firstItem() ?? 0); ?> - <?php echo e($rows->lastItem() ?? 0); ?>

    </div>
    <div>
        <?php echo e($rows->links()); ?>

    </div>
</div>
<?php /**PATH C:\Coding Workplace\Magang PT. Weba Corporation\weba database pc\resources\views/pages/partials/_process_table.blade.php ENDPATH**/ ?>