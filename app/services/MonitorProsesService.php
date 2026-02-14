<?php

// namespace App\Services;

// use Illuminate\Pagination\LengthAwarePaginator;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Facades\DB;

// class MonitorProsesService
// {
//     /**
//      * (DETAIL) panggil SP berat: dbo.sp_monitor_proses
//      */
//     public function fetchRows(string $tanggal, ?string $bomId = null, ?string $connection = null): array
//     {
//         $conn = $connection ? DB::connection($connection) : DB::connection();

//         return $conn->select('EXEC dbo.sp_monitor_proses @Tanggal = ?, @BOMID = ?', [
//             $tanggal,
//             $bomId,
//         ]);
//     }

//     /**
//      * (SUMMARY) panggil SP ringan: dbo.sp_monitor_summary
//      * Return: proses, today, finish (5 baris)
//      */
//     public function fetchSummary(string $tanggal, ?string $connection = null): array
//     {
//         $conn = $connection ? DB::connection($connection) : DB::connection();

//         return $conn->select('EXEC dbo.sp_monitor_summary @Tanggal = ?', [
//             $tanggal,
//         ]);
//     }

//     /**
//      * Map output SP detail -> row format untuk pages.process-detail
//      */
//     public function mapRowToProcess(object $row, string $slug): array
//     {
//         $r = (array) $row;
//         $spk = (int) ($r['QtyBOM'] ?? 0);

//         $base = [
//             'id'          => null,
//             'id_bom'      => $r['BOMID'] ?? null,
//             'po_customer' => $r['POCust'] ?? null,
//             'art_code'    => $r['ArtCode'] ?? null,
//             'art_name'    => $r['ArtName'] ?? null,
//             'size'        => null,
//             'awal'        => null,
//             'akhir'       => null,
//             'spk'         => $spk,
//             'today'       => 0,
//             'finish'      => 0,
//             'balance'     => 0,
//         ];

    //     switch ($slug) {
    //         case 'stitches':
    //             $base['awal']    = $r['TglAwJht'] ?? null;
    //             $base['akhir']   = $r['TglAkhJht'] ?? null;
    //             $base['today']   = (int) ($r['JhtQty'] ?? 0);
    //             $base['finish']  = (int) ($r['JhtFin'] ?? 0);
    //             $base['balance'] = (int) ($r['JhtSal'] ?? ($spk - $base['today'] - $base['finish']));
    //             break;

    //         case 'strobel':
    //             $base['awal']          = $r['TglAwStrob'] ?? null;
    //             $base['akhir']         = $r['TglAkhStrob'] ?? null;
    //             $base['today']         = (int) ($r['StrobQty'] ?? 0);
    //             $base['finish']        = (int) ($r['StrobFin'] ?? 0);
    //             $base['balance']       = (int) ($r['StrobSal'] ?? ($spk - $base['today'] - $base['finish']));
    //             $base['belum_strobel'] = (int) ($r['BlmStrob'] ?? 0);
    //             break;

    //         case 'lasting':
    //             $base['awal']          = $r['TglAwLas'] ?? null;
    //             $base['akhir']         = $r['TglAkhLas'] ?? null;
    //             $base['today']         = (int) ($r['LasQty'] ?? 0);
    //             $base['finish']        = (int) ($r['LasFin'] ?? 0);
    //             $base['balance']       = (int) ($r['LasSal'] ?? ($spk - $base['today'] - $base['finish']));
    //             $base['belum_lasting'] = (int) ($r['BlmLas'] ?? 0);
    //             break;

    //         case 'injection':
    //             $base['awal']         = $r['TglAwInj'] ?? null;
    //             $base['akhir']        = $r['TglAkhInj'] ?? null;
    //             $base['today']        = (int) ($r['InjQty'] ?? 0);
    //             $base['finish']       = (int) ($r['InjFin'] ?? 0);
    //             $base['balance']      = (int) ($r['InjSal'] ?? ($spk - $base['today'] - $base['finish']));
    //             $base['belum_inject'] = (int) ($r['BlmInj'] ?? 0);
    //             break;

    //         case 'finishing':
    //             $base['awal']    = $r['TglAwFin'] ?? null;
    //             $base['akhir']   = $r['TglAkhFin'] ?? null;
    //             $base['today']   = (int) ($r['FinQty'] ?? 0);
    //             $base['finish']  = (int) ($r['FinFin'] ?? 0);
    //             $base['balance'] = (int) ($r['FinSal'] ?? ($spk - $base['today'] - $base['finish']));
    //             break;
    //     }

    //     return $base;
    // }

//     public function paginateCollection(Collection $items, int $perPage, int $page, array $options = []): LengthAwarePaginator
//     {
//         $total = $items->count();
//         $results = $items->forPage($page, $perPage)->values();

//         return new LengthAwarePaginator($results, $total, $perPage, $page, $options);
//     }
// }
