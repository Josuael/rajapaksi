<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Models\Stitches;
use App\Models\Strobel;
use App\Models\Injection;
use App\Models\Lasting;
use App\Models\Finishing;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'time' => now()->toDateTimeString(),
    ]);
});

/**
 * Registry proses: INI yang jadi "relasi" utama berdasarkan slug.
 * Semua endpoint API dan halaman web bisa pakai slug yang sama.
 */
$processRegistry = [
    'stitches' => [
        'title' => 'Stitches',
        'model' => Stitches::class,
        'table' => 'stitches',
        'date_col' => 'awal',      // ganti ke 'akhir' kalau mau
        'today_col' => 'today',
        'finish_col' => 'finish',
        'qty_col' => null,
    ],
    'strobel' => [
        'title' => 'Strobel',
        'model' => Strobel::class,
        'table' => 'strobel',
        'date_col' => 'awal',
        'today_col' => 'today',
        'finish_col' => 'finish',
        'qty_col' => null,
    ],
    'injection' => [
        'title' => 'Injection',
        'model' => Injection::class,
        'table' => 'injection',
        'date_col' => 'awal',
        'today_col' => 'today',
        'finish_col' => 'finish',
        'qty_col' => null,
    ],
    'lasting' => [
        'title' => 'Lasting',
        'model' => Lasting::class,
        'table' => 'lasting',
        'date_col' => 'awal',
        'today_col' => 'today',
        'finish_col' => 'finish',
        'qty_col' => null,
    ],
    'finishing' => [
        'title' => 'Finishing',
        'model' => Finishing::class,
        'table' => 'finishing',
        'date_col' => 'awal',
        'today_col' => 'today',
        'finish_col' => 'finish',
        'qty_col' => null,
    ],
    'recap' => [
        'title' => 'Recap',
        'model' => null,           // belum ada model recap di app/Models
        'table' => 'recap',
        'date_col' => null,        // kalau tabel recap punya kolom tanggal, isi di sini
        'today_col' => null,
        'finish_col' => null,
        'qty_col' => 'qty',        // sesuai controller kamu: sum(qty)
    ],
];

/**
 * Helper: builder aman, pakai Model kalau ada, kalau nggak ya DB::table.
 */
$getBuilder = function (array $meta) {
    if (!empty($meta['model']) && class_exists($meta['model'])) {
        return ($meta['model'])::query();
    }
    return DB::table($meta['table']);
};

/**
 * Helper: cari kolom yang aman buat orderByDesc (biar gak error "Invalid column name 'id'")
 */
$guessOrderColumn = function (string $table): ?string {
    $candidates = ['id', 'id_bom', 'spk', 'awal', 'akhir', 'created_at'];

    foreach ($candidates as $col) {
        try {
            if (Schema::hasColumn($table, $col)) return $col;
        } catch (\Throwable $e) {
            return null;
        }
    }
    return null;
};

/**
 * Helper: sum kolom kalau kolomnya ada, kalau gak ada return 0 (biar API gak 500)
 */
$safeSum = function ($builder, string $table, ?string $col): int {
    if (empty($col)) return 0;

    try {
        if (!Schema::hasColumn($table, $col)) return 0;
    } catch (\Throwable $e) {
        // kalau Schema check gagal (kadang koneksi), tetap coba sum
    }

    try {
        return (int) $builder->sum($col);
    } catch (\Throwable $e) {
        return 0;
    }
};

/**
 * GET /api/processes
 * Return list kartu (mengikuti struktur project kamu: SUM dari tabel masing-masing)
 *
 * Optional:
 * - ?date=YYYY-MM-DD  (kalau mau filter per tanggal, pakai date_col yang tersedia)
 */
Route::get('/processes', function () use ($processRegistry, $getBuilder, $safeSum) {
    $dateParam = request()->query('date'); // optional
    $date = $dateParam ? Carbon::parse($dateParam)->toDateString() : Carbon::today()->toDateString();

    $cards = [];

    foreach ($processRegistry as $slug => $meta) {
        $table = $meta['table'];
        $builder = $getBuilder($meta);

        // kalau user kasih ?date=..., dan date_col ada, kita filter per tanggal
        if (!empty($meta['date_col']) && $dateParam) {
            try {
                if (Schema::hasColumn($table, $meta['date_col'])) {
                    $builder->whereDate($meta['date_col'], $date);
                }
            } catch (\Throwable $e) {
                // ignore, tetap jalan tanpa filter
            }
        }

        // khusus recap: pakai qty
        if (!empty($meta['qty_col'])) {
            $qty = $safeSum($builder, $table, $meta['qty_col']);
            $cards[] = [
                'title'   => $meta['title'],
                'slug'    => $slug,
                'today'   => $qty,
                'finish'  => 0,
                'balance' => 0,
            ];
            continue;
        }

        $todayQty  = $safeSum($builder, $table, $meta['today_col']);
        $finishQty = $safeSum($builder, $table, $meta['finish_col']);
        $balance   = max(0, $todayQty - $finishQty);

        $cards[] = [
            'title'   => $meta['title'],
            'slug'    => $slug,
            'today'   => $todayQty,
            'finish'  => $finishQty,
            'balance' => $balance,
        ];
    }

    return response()->json([
        'date' => $date,
        'filtered_by_date' => (bool) $dateParam,
        'data' => $cards,
    ]);
});

/**
 * GET /api/processes/{slug}
 * Return:
 * - rows: 200 data terakhir
 * - history: agregat 30 hari terakhir (kalau date_col ada)
 */
Route::get('/processes/{slug}', function (string $slug) use ($processRegistry, $getBuilder, $guessOrderColumn) {
    if (!array_key_exists($slug, $processRegistry)) {
        abort(404, 'Process tidak ditemukan');
    }

    $meta = $processRegistry[$slug];
    $table = $meta['table'];

    // rows (latest 200)
    $orderCol = $guessOrderColumn($table);
    $rowsQ = DB::table($table);
    if (!empty($orderCol)) {
        $rowsQ->orderByDesc($orderCol);
    }
    $rows = $rowsQ->limit(200)->get();

    // history 30 hari (kalau punya date_col)
    $from = Carbon::today()->subDays(29)->toDateString();
    $to   = Carbon::today()->toDateString();

    $history = collect();

    $dateCol = $meta['date_col'] ?? null;
    $todayCol = $meta['today_col'] ?? null;
    $finishCol = $meta['finish_col'] ?? null;

    if (!empty($dateCol) && !empty($todayCol) && !empty($finishCol)) {
        try {
            $hasDate = Schema::hasColumn($table, $dateCol);
            $hasToday = Schema::hasColumn($table, $todayCol);
            $hasFinish = Schema::hasColumn($table, $finishCol);

            if ($hasDate && $hasToday && $hasFinish) {
                $history = DB::table($table)
                    ->selectRaw("CAST([$dateCol] AS date) as [date]")
                    ->selectRaw("SUM([$todayCol]) as today_qty")
                    ->selectRaw("SUM([$finishCol]) as finish_qty")
                    ->whereBetween($dateCol, [$from, $to])
                    ->groupBy(DB::raw("CAST([$dateCol] AS date)"))
                    ->orderBy(DB::raw("CAST([$dateCol] AS date)"))
                    ->get()
                    ->map(function ($r) {
                        $todayQty = (int) ($r->today_qty ?? 0);
                        $finishQty = (int) ($r->finish_qty ?? 0);
                        return [
                            'date'       => (string) $r->date,
                            'today_qty'  => $todayQty,
                            'finish_qty' => $finishQty,
                            'balance'    => max(0, $todayQty - $finishQty),
                        ];
                    });
            }
        } catch (\Throwable $e) {
            // kalau schema/grouping error, history kosong tapi endpoint tetap sukses
            $history = collect();
        }
    }

    return response()->json([
        'process' => [
            'title' => $meta['title'],
            'slug'  => $slug,
            'table' => $table,
        ],
        'range' => ['from' => $from, 'to' => $to],
        'sorted_by' => $orderCol,
        'rows_count' => $rows->count(),
        'rows' => $rows,
        'history' => $history,
    ]);
});
