<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class ProcessController extends Controller
{
    private array $processMap = [
        'stitches'  => ['title' => 'Stitches',  'table' => 'stitches'],
        'strobel'   => ['title' => 'Strobel',   'table' => 'strobel'],
        'injection' => ['title' => 'Injection', 'table' => 'injection'],
        'lasting'   => ['title' => 'Lasting',   'table' => 'lasting'],
        'finishing' => ['title' => 'Finishing', 'table' => 'finishing'],
        'recap'     => ['title' => 'Recap',     'table' => 'recap'],
    ];

    private array $processColumns = [
        'stitches' => [
            'bom_id' => 'ID BOM',
            'po_customer' => 'PO Customer',
            'art_code' => 'Art Code',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'awal' => 'Awal',
            'akhir' => 'Akhir',
            'spk' => 'Qty SPK',
            'finish' => 'Finish',
            'today' => 'Today',
            'balance' => 'Balance',
        ],
        'strobel' => [
            'bom_id' => 'ID BOM',
            'po_customer' => 'PO Customer',
            'art_code' => 'Art Code',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'awal' => 'Awal',
            'akhir' => 'Akhir',
            'spk' => 'Qty SPK',
            'belum_strobel' => 'Belum Strobel',
            'finish' => 'Finish',
            'today' => 'Today',
            'balance' => 'Balance',
        ],
        'injection' => [
            'bom_id' => 'ID BOM',
            'po_customer' => 'PO Customer',
            'art_code' => 'Art Code',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'awal' => 'Awal',
            'akhir' => 'Akhir',
            'spk' => 'Qty SPK',
            'belum_inject' => 'Belum Inject',
            'finish' => 'Finish',
            'today' => 'Today',
            'balance' => 'Balance',
        ],
        'lasting' => [
            'bom_id' => 'ID BOM',
            'po_customer' => 'PO Customer',
            'art_code' => 'Art Code',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'awal' => 'Awal',
            'akhir' => 'Akhir',
            'spk' => 'Qty SPK',
            'belum_lasting' => 'Belum Lasting',
            'finish' => 'Finish',
            'today' => 'Today',
            'balance' => 'Balance',
        ],
        'finishing' => [
            'bom_id' => 'ID BOM',
            'po_customer' => 'PO Customer',
            'art_code' => 'Art Code',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'awal' => 'Awal',
            'akhir' => 'Akhir',
            'spk' => 'Qty SPK',
            'belum_finish' => 'Belum Finish',
            'finish' => 'Finish',
            'today' => 'Today',
            'balance' => 'Balance',
        ],

        // recap lokal (kalau table recap ada)
        'recap_table' => [
            'bom_id' => 'ID BOM',
            'art_name' => 'Art Name',
            'size' => 'Size',
            'satuan' => 'Satuan',
            'isi' => 'Isi',
            'qty' => 'Qty',
            'batal' => 'Batal',
            'total' => 'Total',
        ],

        // recap dari T_HslProdDtl
        'recap_thslproddtl' => [
            'id_bom'  => 'ID BOM',
            'art_name'=> 'Art Name',
            'size'    => 'Size',
            'satuan'  => 'Satuan',
            'isi'     => 'Isi',
            'qty'     => 'Qty',
            'batal'   => 'Batal',
            'total'   => 'Total',
        ],
    ];

    private int $maxPaginationPages = 20;

    private string $spMain = 'dbo.SPLRekHslProdBOM_Fast';
    private string $spFallback = 'dbo.SPLRekHslProdBOM';

    /* =========================================================
     * Detector
     * =======================================================*/

    private function procedureExists(string $name): bool
    {
        $parts = explode('.', $name, 2);
        $schema = count($parts) === 2 ? $parts[0] : 'dbo';
        $proc   = count($parts) === 2 ? $parts[1] : $parts[0];

        $row = DB::selectOne("
            SELECT 1 AS ok
            FROM sys.objects o
            INNER JOIN sys.schemas s ON s.schema_id = o.schema_id
            WHERE o.type IN ('P','PC')
              AND s.name = ?
              AND o.name = ?
        ", [$schema, $proc]);

        return (bool) $row;
    }

    private function tableExists(string $name): bool
    {
        $parts = explode('.', $name, 2);
        $schema = count($parts) === 2 ? $parts[0] : 'dbo';
        $table  = count($parts) === 2 ? $parts[1] : $parts[0];

        $row = DB::selectOne("
            SELECT 1 AS ok
            FROM sys.objects o
            INNER JOIN sys.schemas s ON s.schema_id = o.schema_id
            WHERE o.type = 'U'
              AND s.name = ?
              AND o.name = ?
        ", [$schema, $table]);

        return (bool) $row;
    }

    private function tableColumns(string $table): array
    {
        $parts = explode('.', $table, 2);
        $tableName = count($parts) === 2 ? $parts[1] : $parts[0];

        $rows = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = ?
        ", [$tableName]);

        return collect($rows)->map(fn($r) => strtoupper((string) $r->COLUMN_NAME))->values()->all();
    }

    private function getYear(Request $request): int
    {
        $year = (int) $request->query('year', (int)date('Y'));
        if ($year < 2000) $year = 2000;
        if ($year > 2100) $year = 2100;
        return $year;
    }

    private function getWeek(Request $request): ?int
    {
        $w = $request->query('week');
        if ($w === null || $w === '') return null;
        $w = (int)$w;
        if ($w < 1 || $w > 53) return null;
        return $w;
    }

    private function getTgl(Request $request): string
    {
        $tgl = trim((string)$request->query('tgl', ''));
        if ($tgl !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return $tgl;
        }

        $year = $this->getYear($request);
        $week = $this->getWeek($request);

        if ($week !== null) {
            try {
                $dt = new \DateTime();
                $dt->setISODate($year, $week, 1);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return date('Y-m-d');
    }

    private function getPreferredSpName(): ?string
    {
        if ($this->procedureExists($this->spMain)) return $this->spMain;
        if ($this->procedureExists($this->spFallback)) return $this->spFallback;
        return null;
    }

    /* =========================================================
     * YEAR filter (overlap awal/akhir)
     * =======================================================*/

    private function normalizeDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value, $m)) return $m[0];

        $ts = strtotime($value);
        if ($ts === false) return null;

        return date('Y-m-d', $ts);
    }

    private function filterItemsByYear(Collection $items, int $year): Collection
    {
        $from = "{$year}-01-01";
        $to   = "{$year}-12-31";

        return $items->filter(function ($row) use ($from, $to) {
            $awal  = $this->normalizeDate((string) data_get($row, 'awal', ''));
            $akhir = $this->normalizeDate((string) data_get($row, 'akhir', ''));

            if (!$awal && !$akhir) return false;
            if (!$awal)  $awal = $akhir;
            if (!$akhir) $akhir = $awal;

            return $awal <= $to && $akhir >= $from;
        })->values();
    }

    /* =========================================================
     * Per-column filters (collection)
     * =======================================================*/

    private function filterItemsByColumns(
        Collection $items,
        array $allowedCols,
        array $bomKeys,
        array $numericKeys,
        array $bcol,
        array $pcol
    ): Collection {
        $filters = [];

        foreach ($allowedCols as $colKey) {
            $isBom = in_array($colKey, $bomKeys, true);

            $val = $isBom ? ($bcol[$colKey] ?? null) : ($pcol[$colKey] ?? null);
            $val = trim((string) $val);
            if ($val === '') continue;

            $filters[] = [$colKey, $val];
        }

        if (empty($filters)) return $items;

        return $items->filter(function ($row) use ($filters, $numericKeys) {
            foreach ($filters as [$key, $val]) {
                $rowVal = data_get($row, $key);

                if (in_array($key, $numericKeys, true) && is_numeric($val)) {
                    if ($rowVal === null || $rowVal === '') return false;
                    if ((float) $rowVal != (float) $val) return false;
                } else {
                    if (stripos((string) $rowVal, $val) === false) return false;
                }
            }
            return true;
        })->values();
    }

    /**
     * ✅ NEW: per-column filters (single array: col[key])
     * Dipakai sama view baru (kolom filter input: name="col[xxx]").
     */
    private function filterItemsByColumnsSimple(
        Collection $items,
        array $allowedCols,
        array $numericKeys,
        array $col
    ): Collection {
        $filters = [];

        foreach ($allowedCols as $key) {
            if (!array_key_exists($key, $col)) continue;
            $val = trim((string) $col[$key]);
            if ($val === '') continue;
            $filters[] = [$key, $val];
        }

        if (empty($filters)) return $items;

        return $items->filter(function ($row) use ($filters, $numericKeys) {
            foreach ($filters as [$key, $val]) {
                $rowVal = data_get($row, $key);

                if (in_array($key, $numericKeys, true) && is_numeric($val)) {
                    if ($rowVal === null || $rowVal === '') return false;
                    if ((float) $rowVal != (float) $val) return false;
                } else {
                    if (stripos((string) $rowVal, $val) === false) return false;
                }
            }
            return true;
        })->values();
    }

    /* =========================================================
     * Pagination
     * =======================================================*/

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = (int) $request->query('page', 1);
        $page = $page < 1 ? 1 : $page;

        $maxPages = $this->maxPaginationPages;

        $total = $items->count();
        $maxTotal = $perPage * $maxPages;
        $totalCapped = $total > $maxTotal ? $maxTotal : $total;

        $lastPage = (int) ceil(($totalCapped > 0 ? $totalCapped : 1) / $perPage);
        $lastPage = $lastPage < 1 ? 1 : $lastPage;

        if ($page > $maxPages) $page = $maxPages;
        if ($page > $lastPage) $page = $lastPage;

        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $totalCapped,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function paginateQueryNoCountCapped($query, int $perPage, Request $request): LengthAwarePaginator
    {
        $maxPages = $this->maxPaginationPages;

        $page = (int) $request->query('page', 1);
        $page = $page < 1 ? 1 : $page;
        if ($page > $maxPages) $page = $maxPages;

        $items = (clone $query)->forPage($page, $perPage)->get();
        $totalCapped = $perPage * $maxPages;

        return new LengthAwarePaginator(
            $items,
            $totalCapped,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function paginateQueryCapped($query, int $perPage, Request $request): LengthAwarePaginator
    {
        $maxPages = $this->maxPaginationPages;

        $page = (int) $request->query('page', 1);
        $page = $page < 1 ? 1 : $page;

        $totalReal = (clone $query)->count();
        $maxTotal = $perPage * $maxPaginationPages = $this->maxPaginationPages;
        $maxTotal = $perPage * $maxPaginationPages;
        $totalCapped = $totalReal > $maxTotal ? $maxTotal : $totalReal;

        $lastPage = (int) ceil(($totalCapped > 0 ? $totalCapped : 1) / $perPage);
        $lastPage = $lastPage < 1 ? 1 : $lastPage;

        if ($page > $maxPaginationPages) $page = $maxPaginationPages;
        if ($page > $lastPage) $page = $lastPage;

        $items = $query->forPage($page, $perPage)->get();

        return new LengthAwarePaginator(
            $items,
            $totalCapped,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /* =========================================================
     * SORT (NEW)
     * =======================================================*/

    private function getSort(Request $request, array $allowedCols, string $defaultSort): array
    {
        $sort = trim((string) $request->query('sort', ''));
        $dir  = strtolower(trim((string) $request->query('dir', 'asc')));

        if (!in_array($sort, $allowedCols, true)) $sort = $defaultSort;
        if (!in_array($dir, ['asc','desc'], true)) $dir = 'asc';

        return [$sort, $dir];
    }

    private function applySortToCollection(Collection $items, string $sort, string $dir, array $numericKeys = []): Collection
    {
        $isNumeric = in_array($sort, $numericKeys, true);

        $sorted = $items->sortBy(function ($row) use ($sort, $isNumeric) {
            $v = data_get($row, $sort);
            if ($isNumeric) return (float) ($v ?? 0);
            return mb_strtolower((string) ($v ?? ''));
        });

        return $dir === 'desc' ? $sorted->reverse()->values() : $sorted->values();
    }

    private function applySortToQuery($query, string $field, string $dir)
    {
        return $query->orderBy($field, $dir);
    }

    /* =========================================================
     * Size hydrator
     * =======================================================*/

    private function hydrateSizeFromBomDtl(LengthAwarePaginator $rows, string $bomKey = 'bom_id'): void
    {
        if (!$this->tableExists('T_BOMDtl')) return;

        $items = collect($rows->items());
        if ($items->isEmpty()) return;

        $bomIds = $items->map(fn($r) => data_get($r, $bomKey))->filter()->unique()->values()->all();
        if (empty($bomIds)) return;

        $sizeMap = DB::table('T_BOMDtl')
            ->selectRaw('BOMID, MIN(Uk) as Uk')
            ->whereIn('BOMID', $bomIds)
            ->groupBy('BOMID')
            ->pluck('Uk', 'BOMID');

        foreach ($rows->items() as $r) {
            $id = data_get($r, $bomKey);
            if (!$id) continue;

            $uk = $sizeMap[$id] ?? null;
            if ($uk !== null && $uk !== '') {
                $r->size = (string) $uk;
            }
        }
    }

    /* =========================================================
     * SP rows + mapper
     * =======================================================*/

    private function spRows(Request $request): Collection
    {
        $spName = $this->getPreferredSpName();
        if (!$spName) {
            abort(500, "Stored Procedure {$this->spMain} / {$this->spFallback} tidak ditemukan.");
        }

        $tgl = $this->getTgl($request);
        $cacheKey = "sp_big_rows:sp={$spName}:tgl={$tgl}";

        return Cache::remember($cacheKey, 60, function () use ($tgl, $spName) {
            return collect(DB::select("EXEC {$spName} @Tgl = ?", [$tgl]));
        });
    }

    private function mapSpRowToProcess(object $r, string $slug): object
    {
        $size = $r->Size ?? $r->UK ?? $r->Uk ?? '';

        $base = [
            'bom_id'      => $r->BOMID ?? null,
            'po_customer' => $r->POCust ?? null,
            'art_code'    => $r->ArtCode ?? null,
            'art_name'    => $r->ArtName ?? null,
            'size'        => (string)$size,
            'spk'         => $r->QtyBOM ?? null,
        ];

        if ($slug === 'stitches') {
            return (object) array_merge($base, [
                'awal'    => $r->TglAwJht ?? null,
                'akhir'   => $r->TglAkhJht ?? null,
                'today'   => (float) ($r->JhtQty ?? 0),
                'finish'  => (float) ($r->JhtFin ?? 0),
                'balance' => (float) ($r->JhtSal ?? 0),
            ]);
        }

        if ($slug === 'strobel') {
            return (object) array_merge($base, [
                'awal'          => $r->TglAwStrob ?? null,
                'akhir'         => $r->TglAkhStrob ?? null,
                'belum_strobel' => (float) ($r->BlmStrob ?? 0),
                'today'         => (float) ($r->StrobQty ?? 0),
                'finish'        => (float) ($r->StrobFin ?? 0),
                'balance'       => (float) ($r->StrobSal ?? 0),
            ]);
        }

        if ($slug === 'lasting') {
            return (object) array_merge($base, [
                'awal'          => $r->TglAwLas ?? null,
                'akhir'         => $r->TglAkhLas ?? null,
                'belum_lasting' => (float) ($r->BlmLas ?? 0),
                'today'         => (float) ($r->LasQty ?? 0),
                'finish'        => (float) ($r->LasFin ?? 0),
                'balance'       => (float) ($r->LasSal ?? 0),
            ]);
        }

        if ($slug === 'injection') {
            return (object) array_merge($base, [
                'awal'         => $r->TglAwInj ?? null,
                'akhir'        => $r->TglAkhInj ?? null,
                'belum_inject' => (float) ($r->BlmInj ?? 0),
                'today'        => (float) ($r->InjQty ?? 0),
                'finish'       => (float) ($r->InjFin ?? 0),
                'balance'      => (float) ($r->InjSal ?? 0),
            ]);
        }

        return (object) array_merge($base, [
            'awal'        => $r->TglAwFin ?? null,
            'akhir'       => $r->TglAkhFin ?? null,
            'belum_finish'=> (float) ($r->BlmFin ?? 0),
            'today'       => (float) ($r->FinQty ?? 0),
            'finish'      => (float) ($r->FinFin ?? 0),
            'balance'     => (float) ($r->FinSal ?? 0),
        ]);
    }

    private function cachedProcessItemsBase(string $slug, Request $request): Collection
    {
        $ttl = 60;
        $tgl = $this->getTgl($request);
        $cacheKey = "sp_items_base:{$slug}:tgl={$tgl}";

        $arr = Cache::remember($cacheKey, $ttl, function () use ($slug, $request) {
            $rows = $this->spRows($request);
            return $rows->map(fn($r) => $this->mapSpRowToProcess($r, $slug))->values()->all();
        });

        return collect($arr);
    }

    private function cachedProcessItemsFiltered(string $slug, string $q, Request $request): Collection
    {
        $ttl = 30;
        $tgl = $this->getTgl($request);
        $cacheKey = "sp_items_filtered:{$slug}:tgl={$tgl}:q=" . md5($q);

        $arr = Cache::remember($cacheKey, $ttl, function () use ($slug, $q, $request) {
            $base = $this->cachedProcessItemsBase($slug, $request);
            if ($q === '') return $base->values()->all();

            $qLower = mb_strtolower($q);
            return $base->filter(function ($item) use ($qLower) {
                foreach ($item as $v) {
                    if ($v !== null && stripos((string)$v, $qLower) !== false) return true;
                }
                return false;
            })->values()->all();
        });

        return collect($arr);
    }

    /* =========================================================
     * RECAP query from T_HslProdDtl (with tanggal single + process filter + sort)
     * =======================================================*/

    private function recapQueryThslProdDtl(Request $request)
    {
        $table = 'T_HslProdDtl';
        $colsDtl = $this->tableColumns($table);

        $pick = function(array $colsUpper, array $candidates) {
            foreach ($candidates as $c) {
                if (in_array(strtoupper($c), $colsUpper, true)) return $c;
            }
            return null;
        };

        $colBom     = $pick($colsDtl, ['BOMID','BOMId','BomID']);
        $colArtCode = $pick($colsDtl, ['ARTCODE','ArtCode','artcode']);
        $colArtName = $pick($colsDtl, ['ARTNAME','ArtName','artname']);
        $colQty     = $pick($colsDtl, ['QTY','Qty','qty']);
        $colBatal   = $pick($colsDtl, ['BATAL','Batal','batal']);
        $colTot     = $pick($colsDtl, ['TOT','Tot','Total','total']);
        $colTanggal = $pick($colsDtl, ['TANGGAL','Tanggal','tanggal']);
        $colJam     = $pick($colsDtl, ['JAM','Jam','jam']);
        $colProses  = $pick($colsDtl, ['PROSES','Proses','proses']);

        // join M_Brg
        $joinBrg = $this->tableExists('M_Brg');
        $colsBrg = $joinBrg ? $this->tableColumns('M_Brg') : [];
        $colBrgArtCode = $pick($colsBrg, ['ARTCODE','ArtCode','artcode']);
        $colBrgArtName = $pick($colsBrg, ['ARTNAME','ArtName','artname']);
        $colBrgAss     = $pick($colsBrg, ['ASS','Ass','ASSID','AssID']);

        // join M_BrgAss (SATID, ISI) via Ass
        $joinAss = $this->tableExists('M_BrgAss');
        $colsAss = $joinAss ? $this->tableColumns('M_BrgAss') : [];
        $colAssAss   = $pick($colsAss, ['ASS','Ass']);
        $colSatID    = $pick($colsAss, ['SATID','SatID','satid']);
        $colIsiAss   = $pick($colsAss, ['ISI','Isi','isi']);

        // join BOMDtl untuk size
        $joinBomDtl = $this->tableExists('T_BOMDtl');
        $colsBomDtl = $joinBomDtl ? $this->tableColumns('T_BOMDtl') : [];
        $colBomDtlBom = $pick($colsBomDtl, ['BOMID','BOMId','BomID']);
        $colUk        = $pick($colsBomDtl, ['UK','Uk','uk']);

        $assUsable = $joinAss && $joinBrg && $colBrgAss && $colAssAss && ($colSatID || $colIsiAss);

        $select = [];
        $select[] = $colBom ? "d.{$colBom} as id_bom" : "CAST(NULL AS VARCHAR(50)) as id_bom";

        if ($colArtName) $select[] = "d.{$colArtName} as art_name";
        elseif ($joinBrg && $colBrgArtName) $select[] = "mb.{$colBrgArtName} as art_name";
        else $select[] = "CAST(NULL AS VARCHAR(200)) as art_name";

        if ($joinBomDtl && $colUk) $select[] = "bd.{$colUk} as size";
        else $select[] = "CAST(NULL AS VARCHAR(50)) as size";

        $select[] = ($assUsable && $colSatID)  ? "ma.{$colSatID} as satuan" : "CAST(NULL AS VARCHAR(50)) as satuan";
        $select[] = ($assUsable && $colIsiAss) ? "ma.{$colIsiAss} as isi"    : "CAST(NULL AS VARCHAR(50)) as isi";

        $select[] = $colQty   ? "d.{$colQty} as qty"     : "CAST(0 AS DECIMAL(18,2)) as qty";
        $select[] = $colBatal ? "d.{$colBatal} as batal" : "CAST(0 AS DECIMAL(18,2)) as batal";
        $select[] = $colTot   ? "d.{$colTot} as total"   : "CAST(0 AS DECIMAL(18,2)) as total";

        $query = DB::table($table . ' as d')->selectRaw(implode(",\n", $select));

        if ($joinBrg && $colArtCode && $colBrgArtCode) {
            $query->leftJoin('M_Brg as mb', "mb.{$colBrgArtCode}", '=', "d.{$colArtCode}");
        }

        if ($joinBomDtl && $colBom && $colBomDtlBom && $colUk) {
            $query->leftJoin('T_BOMDtl as bd', "bd.{$colBomDtlBom}", '=', "d.{$colBom}");
        }

        if ($assUsable) {
            $query->leftJoin('M_BrgAss as ma', "ma.{$colAssAss}", '=', "mb.{$colBrgAss}");
        }

        // tanggal range (from/to)
        $tanggalFrom = $request->query('tanggal_from');
        $tanggalTo   = $request->query('tanggal_to');

        if ($colTanggal && ($tanggalFrom || $tanggalTo)) {
            if ($tanggalFrom && $tanggalTo) {
                // end exclusive biar aman (kalau tanggal kolom datetime)
                $toExclusive = date('Y-m-d', strtotime($tanggalTo . ' +1 day'));
                $query->where("d.{$colTanggal}", '>=', $tanggalFrom)
                    ->where("d.{$colTanggal}", '<',  $toExclusive);
            } elseif ($tanggalFrom) {
                $query->where("d.{$colTanggal}", '>=', $tanggalFrom);
            } elseif ($tanggalTo) {
                $toExclusive = date('Y-m-d', strtotime($tanggalTo . ' +1 day'));
                $query->where("d.{$colTanggal}", '<',  $toExclusive);
            }
        }

        // process filter recap
        $process = trim((string) $request->query('process', ''));
        if ($process !== '' && $colProses) {
            $query->where("d.{$colProses}", '=', $process);
        }

        // per-column filter recap
        $allowed = array_keys($this->processColumns['recap_thslproddtl']);
        $col = (array) $request->query('col', []);
        $numericCols = ['isi', 'qty', 'batal', 'total'];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $col)) continue;
            $val = trim((string) $col[$key]);
            if ($val === '') continue;

            $field = null;
            if ($key === 'id_bom' && $colBom) $field = "d.{$colBom}";
            if ($key === 'art_name') $field = $colArtName ? "d.{$colArtName}" : (($joinBrg && $colBrgArtName) ? "mb.{$colBrgArtName}" : null);
            if ($key === 'size') $field = ($joinBomDtl && $colUk) ? "bd.{$colUk}" : null;
            if ($key === 'satuan') $field = ($assUsable && $colSatID) ? "ma.{$colSatID}" : null;
            if ($key === 'isi') $field = ($assUsable && $colIsiAss) ? "ma.{$colIsiAss}" : null;
            if ($key === 'qty' && $colQty) $field = "d.{$colQty}";
            if ($key === 'batal' && $colBatal) $field = "d.{$colBatal}";
            if ($key === 'total' && $colTot) $field = "d.{$colTot}";

            if (!$field) continue;

            if (in_array($key, $numericCols, true) && is_numeric($val)) {
                $query->where($field, '=', $val);
            } else {
                $query->where($field, 'like', "%{$val}%");
            }
        }

        // SORT recap THslProdDtl
        $allowedSort = array_keys($this->processColumns['recap_thslproddtl']);
        [$sort, $dir] = $this->getSort($request, $allowedSort, 'id_bom');

        $sortField = null;
        if ($sort === 'id_bom' && $colBom) $sortField = "d.{$colBom}";
        if ($sort === 'art_name') $sortField = $colArtName ? "d.{$colArtName}" : (($joinBrg && $colBrgArtName) ? "mb.{$colBrgArtName}" : null);
        if ($sort === 'size') $sortField = ($joinBomDtl && $colUk) ? "bd.{$colUk}" : null;
        if ($sort === 'satuan') $sortField = ($assUsable && $colSatID) ? "ma.{$colSatID}" : null;
        if ($sort === 'isi') $sortField = ($assUsable && $colIsiAss) ? "ma.{$colIsiAss}" : null;
        if ($sort === 'qty' && $colQty) $sortField = "d.{$colQty}";
        if ($sort === 'batal' && $colBatal) $sortField = "d.{$colBatal}";
        if ($sort === 'total' && $colTot) $sortField = "d.{$colTot}";

        if ($sortField) {
            $query->orderBy($sortField, $dir);
        } else {
            if ($colTanggal) {
                $query->orderByDesc("d.{$colTanggal}");
                if ($colJam) $query->orderByDesc("d.{$colJam}");
            } elseif ($colBom) {
                $query->orderBy("d.{$colBom}");
            }
        }

        return $query;
    }

    /* =========================================================
     * HOME
     * =======================================================*/

    public function home(Request $request)
    {
        set_time_limit(300);

        $spName = $this->getPreferredSpName();

        if ($spName) {
            $rows = $this->spRows($request);

            $sumFor = function(string $slug) use ($rows) {
                $mapped = $rows->map(fn($r) => $this->mapSpRowToProcess($r, $slug));
                return [
                    'today'   => (int) round($mapped->sum('today')),
                    'finish'  => (int) round($mapped->sum('finish')),
                    'balance' => (int) round($mapped->sum('balance')),
                ];
            };

            $st = $sumFor('stitches');
            $sb = $sumFor('strobel');
            $ls = $sumFor('lasting');
            $ij = $sumFor('injection');
            $fn = $sumFor('finishing');

            $cards = [
                ['slug' => 'stitches',  'title' => 'Stitches',  'today' => $st['today'], 'finish' => $st['finish'], 'balance' => $st['balance']],
                ['slug' => 'strobel',   'title' => 'Strobel',   'today' => $sb['today'], 'finish' => $sb['finish'], 'balance' => $sb['balance']],
                ['slug' => 'lasting',   'title' => 'Lasting',   'today' => $ls['today'], 'finish' => $ls['finish'], 'balance' => $ls['balance']],
                ['slug' => 'injection', 'title' => 'Injection', 'today' => $ij['today'], 'finish' => $ij['finish'], 'balance' => $ij['balance']],
                ['slug' => 'finishing', 'title' => 'Finishing', 'today' => $fn['today'], 'finish' => $fn['finish'], 'balance' => $fn['balance']],
                ['slug' => 'recap',     'title' => 'Recap',     'today' => 0, 'finish' => 0, 'balance' => 0],
            ];

            return view('pages.home', compact('cards'));
        }

        $cards = [];
        foreach ($this->processMap as $slug => $meta) {
            $table = $meta['table'];

            if (!$this->tableExists($table)) {
                $cards[] = ['slug' => $slug, 'title' => $meta['title'], 'today' => 0, 'finish' => 0, 'balance' => 0];
                continue;
            }

            $agg = ($slug === 'recap')
                ? DB::table($table)->selectRaw('COALESCE(SUM(qty),0) as today, COALESCE(SUM(batal),0) as finish, COALESCE(SUM(total),0) as balance')->first()
                : DB::table($table)->selectRaw('COALESCE(SUM(today),0) as today, COALESCE(SUM(finish),0) as finish, COALESCE(SUM(balance),0) as balance')->first();

            $cards[] = [
                'slug' => $slug,
                'title' => $meta['title'],
                'today' => (int) ($agg->today ?? 0),
                'finish' => (int) ($agg->finish ?? 0),
                'balance' => (int) ($agg->balance ?? 0),
            ];
        }

        return view('pages.home', compact('cards'));
    }

    /* =========================================================
     * ASSEMBLY
     * =======================================================*/

    public function assembly(Request $request, ?string $tab = null)
    {
        set_time_limit(300);

        $tab = $tab ?: $request->query('tab', 'injection');

        $title = 'Assembly';
        $activeTab = $tab;
        $slug = $tab;

        if (!isset($this->processMap[$slug])) abort(404, 'Process tidak ditemukan');

        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $q = trim((string) $request->query('q', ''));
        $year = $this->getYear($request);

        $subtitle = $this->processMap[$slug]['title'];
        $columns  = $this->processColumns[$slug];
        $allowedCols = array_keys($columns);

        // ✅ view kolom filter pakai name="col[xxx]" (satu array)
        $col = (array) $request->query('col', []);

        $bomKeys = ['id_bom', 'bom_id', 'po_customer', 'art_code', 'art_name', 'size', 'qty_spk'];
        $numericKeys = [
            'bom_id', 'id_bom',
            'spk', 'finish', 'today', 'balance',
            'belum_inject', 'belum_lasting', 'belum_strobel', 'belum_finish'
        ];

        if ($this->getPreferredSpName()) {
            $items = $this->cachedProcessItemsFiltered($slug, $q, $request);

            $items = $this->filterItemsByYear($items, $year);
            $items = $this->filterItemsByColumnsSimple($items, $allowedCols, $numericKeys, $col);

            // SORT
            [$sort, $dir] = $this->getSort($request, $allowedCols, 'bom_id');
            $items = $this->applySortToCollection($items, $sort, $dir, $numericKeys);

            $rows = $this->paginateCollection($items, $perPage, $request)->withQueryString();
            $this->hydrateSizeFromBomDtl($rows, 'bom_id');

            return view('pages.assembly', compact('title','subtitle','activeTab','slug','rows','columns'));
        }

        $table = $this->processMap[$slug]['table'];
        if (!$this->tableExists($table)) abort(500, "Sumber data Assembly '{$slug}' tidak tersedia.");

        $bomTable = 'dbo.bom';
        $bomPk = 'id';

        $query = DB::table($table . ' as p')
            ->leftJoin($bomTable . ' as b', 'b.' . $bomPk, '=', 'p.bom_id')
            ->select([
                'p.*',
                'b.' . $bomPk . ' as id_bom',
                'b.po_customer',
                'b.art_code',
                'b.art_name',
                'b.size',
                'b.qty_spk',
            ]);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('b.po_customer', 'like', "%{$q}%")
                    ->orWhere('b.art_code', 'like', "%{$q}%")
                    ->orWhere('b.art_name', 'like', "%{$q}%")
                    ->orWhere('b.size', 'like', "%{$q}%")
                    ->orWhere('p.bom_id', 'like', "%{$q}%");
            });
        }

        foreach ($allowedCols as $colKey) {
            $val = $col[$colKey] ?? null;
            $val = trim((string) $val);
            if ($val === '') continue;

            $isBom = in_array($colKey, $bomKeys, true);

            $field = $isBom
                ? ($colKey === 'bom_id' ? 'p.bom_id' : ($colKey === 'id_bom' ? 'b.' . $bomPk : 'b.' . $colKey))
                : 'p.' . $colKey;

            if (in_array($colKey, $numericKeys, true) && is_numeric($val)) $query->where($field, '=', $val);
            else $query->where($field, 'like', "%{$val}%");
        }

        // SORT fallback query
        [$sort, $dir] = $this->getSort($request, $allowedCols, 'bom_id');
        $field = in_array($sort, $bomKeys, true)
            ? ($sort === 'bom_id' ? 'p.bom_id' : ($sort === 'id_bom' ? 'b.' . $bomPk : 'b.' . $sort))
            : 'p.' . $sort;

        $query = $this->applySortToQuery($query, $field, $dir);

        $rows = $this->paginateQueryNoCountCapped($query, $perPage, $request)->withQueryString();
        $this->hydrateSizeFromBomDtl($rows, 'bom_id');

        return view('pages.assembly', compact('title','subtitle','activeTab','slug','rows','columns'));
    }

    /* =========================================================
     * SHOW
     * =======================================================*/

    public function show(Request $request, string $slug)
    {
        set_time_limit(300);

        if (!isset($this->processMap[$slug])) abort(404, 'Process tidak ditemukan');

        $title    = $this->processMap[$slug]['title'];
        $subtitle = $title;

        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        // RECAP
        if ($slug === 'recap') {
            $recapTable = $this->processMap[$slug]['table'];

            // A) recap lokal
            if ($this->tableExists($recapTable)) {
                $table = $recapTable;

                $columns     = $this->processColumns['recap_table'];
                $allowedCols = array_keys($columns);

                $query = DB::table($table);

                // tanggal range (from/to)
                $tanggalFrom = $request->query('tanggal_from');
                $tanggalTo   = $request->query('tanggal_to');

                if ($tanggalFrom && $tanggalTo) {
                    $query->whereBetween('tanggal', [$tanggalFrom, $tanggalTo]);
                } elseif ($tanggalFrom) {
                    $query->whereDate('tanggal', '>=', $tanggalFrom);
                } elseif ($tanggalTo) {
                    $query->whereDate('tanggal', '<=', $tanggalTo);
                }

                $hasProcessCol  = Schema::hasColumn($table, 'process');
                $processOptions = collect();

                if ($hasProcessCol) {
                    $process = $request->query('process');
                    if (!empty($process)) $query->where('process', $process);

                    $processOptions = DB::table($table)
                        ->select('process')->whereNotNull('process')->distinct()
                        ->orderBy('process')->pluck('process');
                }

                // per-column filters
                $col = (array) $request->query('col', []);
                $numericCols = ['isi', 'qty', 'batal', 'total'];
                foreach ($allowedCols as $key) {
                    if (!array_key_exists($key, $col)) continue;
                    $val = trim((string) $col[$key]);
                    if ($val === '') continue;

                    if (in_array($key, $numericCols, true) && is_numeric($val)) $query->where($key, '=', $val);
                    else $query->where($key, 'like', "%{$val}%");
                }

                // SORT recap lokal
                [$sort, $dir] = $this->getSort($request, $allowedCols, 'bom_id');
                if (Schema::hasColumn($table, $sort)) {
                    $query->orderBy($sort, $dir);
                } else {
                    $query->orderBy('bom_id', 'asc');
                }

                $rows = $query->paginate($perPage)->withQueryString();

                return view('pages.process-detail', compact(
                    'title','subtitle','slug','rows','columns','processOptions','hasProcessCol'
                ));
            }

            // B) recap dari T_HslProdDtl
            if (!$this->tableExists('T_HslProdDtl')) {
                abort(500, "Recap source tidak ditemukan: table 'recap' tidak ada dan 'T_HslProdDtl' juga tidak ada.");
            }

            $columns = $this->processColumns['recap_thslproddtl'];

            // dropdown process options (filter)
            $processOptions = collect();
            $hasProcessCol = false;

            $colsDtl = $this->tableColumns('T_HslProdDtl');
            $pick = function(array $colsUpper, array $candidates) {
                foreach ($candidates as $c) {
                    if (in_array(strtoupper($c), $colsUpper, true)) return $c;
                }
                return null;
            };
            $colProses = $pick($colsDtl, ['PROSES','Proses','proses']);

            if ($colProses) {
                $hasProcessCol = true;
                $processOptions = DB::table('T_HslProdDtl')
                    ->select($colProses)
                    ->whereNotNull($colProses)
                    ->distinct()
                    ->orderBy($colProses)
                    ->pluck($colProses);
            }

            $rows = $this->paginateQueryCapped(
                $this->recapQueryThslProdDtl($request),
                $perPage,
                $request
            )->withQueryString();

            return view('pages.process-detail', compact('title','subtitle','slug','rows','columns','processOptions','hasProcessCol'));
        }

        // NON-RECAP
        $columns = $this->processColumns[$slug];
        $allowedCols = array_keys($columns);

        $q = trim((string) $request->query('q', ''));
        $year = $this->getYear($request);

        // ✅ view kolom filter pakai name="col[xxx]" (satu array)
        $col = (array) $request->query('col', []);

        $bomKeys = ['id_bom', 'bom_id', 'po_customer', 'art_code', 'art_name', 'size', 'qty_spk'];
        $numericKeys = [
            'bom_id', 'id_bom',
            'spk', 'finish', 'today', 'balance',
            'belum_inject', 'belum_lasting', 'belum_strobel', 'belum_finish'
        ];

        // 1) prefer SP
        if ($this->getPreferredSpName()) {
            $items = $this->cachedProcessItemsFiltered($slug, $q, $request);

            $items = $this->filterItemsByYear($items, $year);
            $items = $this->filterItemsByColumnsSimple($items, $allowedCols, $numericKeys, $col);

            // SORT
            [$sort, $dir] = $this->getSort($request, $allowedCols, 'bom_id');
            $items = $this->applySortToCollection($items, $sort, $dir, $numericKeys);

            $rows = $this->paginateCollection($items, $perPage, $request)->withQueryString();
            $this->hydrateSizeFromBomDtl($rows, 'bom_id');

            return view('pages.process-detail', compact('title','subtitle','slug','rows','columns'));
        }

        // 2) fallback table
        $table = $this->processMap[$slug]['table'];
        if (!$this->tableExists($table)) abort(500, "Sumber data process '{$slug}' tidak tersedia.");

        $bomTable = 'dbo.bom';
        $bomPk    = 'id';

        $query = DB::table($table . ' as p')
            ->leftJoin($bomTable . ' as b', 'b.' . $bomPk, '=', 'p.bom_id')
            ->select([
                'p.*',
                'b.' . $bomPk . ' as id_bom',
                'b.po_customer',
                'b.art_code',
                'b.art_name',
                'b.size',
                'b.qty_spk',
            ]);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('b.po_customer', 'like', "%{$q}%")
                    ->orWhere('b.art_code', 'like', "%{$q}%")
                    ->orWhere('b.art_name', 'like', "%{$q}%")
                    ->orWhere('b.size', 'like', "%{$q}%")
                    ->orWhere('p.bom_id', 'like', "%{$q}%");
            });
        }

        foreach ($allowedCols as $colKey) {
            $val = $col[$colKey] ?? null;
            $val = trim((string) $val);
            if ($val === '') continue;

            $isBom = in_array($colKey, $bomKeys, true);

            $field = $isBom
                ? ($colKey === 'bom_id' ? 'p.bom_id' : ($colKey === 'id_bom' ? 'b.' . $bomPk : 'b.' . $colKey))
                : 'p.' . $colKey;

            if (in_array($colKey, $numericKeys, true) && is_numeric($val)) $query->where($field, '=', $val);
            else $query->where($field, 'like', "%{$val}%");
        }

        // SORT fallback query
        [$sort, $dir] = $this->getSort($request, $allowedCols, 'bom_id');
        $field = in_array($sort, $bomKeys, true)
            ? ($sort === 'bom_id' ? 'p.bom_id' : ($sort === 'id_bom' ? 'b.' . $bomPk : 'b.' . $sort))
            : 'p.' . $sort;

        $query = $this->applySortToQuery($query, $field, $dir);

        $rows = $query->paginate($perPage)->withQueryString();
        $this->hydrateSizeFromBomDtl($rows, 'bom_id');

        return view('pages.process-detail', compact('title','subtitle','slug','rows','columns'));
    }

    /* =========================================================
     * AUTOCOMPLETE / SUGGESTIONS (AJAX)
     * =======================================================*/

    public function suggest(Request $request, string $slug)
    {
        if (!isset($this->processMap[$slug])) {
            return response()->json([]);
        }

        $col = trim((string) $request->query('col', ''));
        $q   = trim((string) $request->query('q', ''));

        // limit biar ringan
        $limit = (int) $request->query('limit', 10);
        if ($limit < 1) $limit = 10;
        if ($limit > 20) $limit = 20;

        // recap beda table + banyak join, skip dulu (ga diminta)
        if ($slug === 'recap') {
            return response()->json([]);
        }

        $allowedCols = array_keys($this->processColumns[$slug]);
        if (!in_array($col, $allowedCols, true)) {
            return response()->json([]);
        }

        // 1) prefer SP (super cepat karena sudah ada cached base rows)
        if ($this->getPreferredSpName()) {
            $items = $this->cachedProcessItemsBase($slug, $request);

            $out = $items
                ->map(fn($r) => (string) (data_get($r, $col) ?? ''))
                ->filter(fn($v) => $v !== '')
                ->unique();

            if ($q !== '') {
                $qLower = mb_strtolower($q);
                $out = $out->filter(fn($v) => stripos($v, $qLower) !== false);
            }

            return response()->json($out->take($limit)->values()->all());
        }

        // 2) fallback table
        $table = $this->processMap[$slug]['table'] ?? null;
        if (!$table || !$this->tableExists($table)) {
            return response()->json([]);
        }

        $bomKeys = ['id_bom', 'bom_id', 'po_customer', 'art_code', 'art_name', 'size', 'qty_spk'];

        $bomTable = 'dbo.bom';
        $bomPk    = 'id';

        $query = DB::table($table . ' as p')
            ->leftJoin($bomTable . ' as b', 'b.' . $bomPk, '=', 'p.bom_id');

        $field = in_array($col, $bomKeys, true)
            ? ($col === 'bom_id' ? 'p.bom_id' : ($col === 'id_bom' ? 'b.' . $bomPk : 'b.' . $col))
            : 'p.' . $col;

        $query->select($field . ' as v')->whereNotNull($field);

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $vals = $query->distinct()->limit($limit)->pluck('v');
        return response()->json($vals->filter()->values()->all());
    }
}
