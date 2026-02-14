{{-- resources/views/pages/partials/_process_table.blade.php --}}

@php
    $currentSort = request('sort', '');
    $currentDir  = request('dir', 'asc');
@endphp

<div class="table-responsive w-100">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                @foreach($columns as $key => $label)
                    @php
                        $isActive = ($currentSort === $key);
                        $nextDir  = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';

                        $query = request()->query();
                        $query['sort'] = $key;
                        $query['dir']  = $nextDir;
                        $query['page'] = 1;

                        $href = url()->current() . '?' . http_build_query($query);
                    @endphp

                    <th class="text-nowrap">
                        <a href="{{ $href }}"
                           class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                            <span>{{ $label }}</span>

                            @if($isActive)
                                @if($currentDir === 'asc')
                                    <i class="bi bi-caret-up-fill"></i>
                                @else
                                    <i class="bi bi-caret-down-fill"></i>
                                @endif
                            @else
                                <i class="bi bi-arrow-down-up text-muted"></i>
                            @endif
                        </a>
                    </th>
                @endforeach
            </tr>

            {{-- Column Filters: no nested form --}}
            <tr>
                @foreach($columns as $key => $label)
                    <th>
                        <input
                            type="text"
                            name="col[{{ $key }}]"
                            value="{{ request('col.'.$key) }}"
                            class="form-control form-control-sm"
                            placeholder="Search {{ $label }}"
                            list="dl-{{ $key }}"
                            data-suggest-col="{{ $key }}"
                            oninput="window.__webaSuggest?.handleInput(this)"
                            onchange="document.getElementById('filterForm')?.submit()"
                        />

                        {{-- ✅ HTML5 datalist buat suggestion (diisi via AJAX) --}}
                        <datalist id="dl-{{ $key }}"></datalist>
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $row)
                <tr data-row style="cursor:pointer;">
                    @foreach($columns as $key => $label)
                        <td class="text-nowrap">
                            {{ data_get($row, $key) }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center py-4 text-muted">
                        No data found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@once
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
@endonce

<div class="d-flex justify-content-between align-items-center mt-3 w-100">
    <div class="text-muted small">
        Showing {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }}
    </div>
    <div>
        {{ $rows->links() }}
    </div>
</div>
