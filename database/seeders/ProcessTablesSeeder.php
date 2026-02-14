<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ProcessTablesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $tables = [
            'stitches'  => ['belum_col' => null],
            'strobel'   => ['belum_col' => 'belum_strobel'],
            'injection' => ['belum_col' => 'belum_inject'],
            'lasting'   => ['belum_col' => 'belum_lasting'],
            'finishing' => ['belum_col' => 'belum_finish'],
            'recap'     => ['belum_col' => null],
        ];

        foreach ($tables as $table => $meta) {
            // kalau table gak ada, skip
            if (!$this->tableExists($table)) {
                continue;
            }

            // Kosongin data dulu (opsional)
            try {
                DB::table($table)->delete();
            } catch (\Throwable $e) {
                // kalau delete keblok (FK/permission), skip table
                continue;
            }

            $baseDate = Carbon::today()->subDays(30);
            $rows = [];

            for ($i = 1; $i <= 200; $i++) {
                $awal = $baseDate->copy()->addDays(rand(0, 25));
                $akhir = $awal->copy()->addDays(rand(0, 5));

                // angka produksi
                $today = rand(0, 500);
                $finish = rand(0, $today);
                $balance = max(0, $today - $finish);

                // common fields (tapi kita akan filter hanya yang exist di table)
                $common = [
                    'id_bom' => 'BOM-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT),
                    'po_customer' => 'PO-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT),
                    'art_code' => 'ART-' . strtoupper($faker->bothify('??###')),
                    'art_name' => $faker->words(3, true),
                    'size' => rand(35, 46),
                    'spk' => rand(100000, 999999),
                    'awal' => $awal->toDateString(),
                    'akhir' => $akhir->toDateString(),
                ];

                if ($table === 'recap') {
                    $qty = rand(0, 500);
                    $batal = rand(0, (int) floor($qty * 0.2));
                    $isi = rand(1, 12);
                    $total = max(0, $qty - $batal) * $isi;

                    $row = [
                        'id_bom' => $common['id_bom'],
                        'art_name' => $common['art_name'],
                        'size' => $common['size'],
                        'satuan' => $faker->randomElement(['PCS', 'PAIR', 'SET']),
                        'isi' => $isi,
                        'qty' => $qty,
                        'batal' => $batal,
                        'total' => $total,
                    ];
                } else {
                    $row = array_merge($common, [
                        'finish' => $finish,
                        'today' => $today,
                        'balance' => $balance,
                    ]);

                    // belum_* kalau table punya kolomnya
                    if (!empty($meta['belum_col'])) {
                        $row[$meta['belum_col']] = rand(0, 500);
                    }
                }

                // IMPORTANT: jangan pernah insert "id"
                unset($row['id']);

                // filter: hanya insert kolom yang benar-benar ada di table
                $row = $this->filterExistingColumns($table, $row);

                $rows[] = $row;
            }

            // insert batch
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            // SQL Server usually schema dbo
            return Schema::hasTable($table) || Schema::hasTable('dbo.' . $table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Buang keys yang gak ada di table biar gak error "Invalid column name"
     */
    private function filterExistingColumns(string $table, array $row): array
    {
        $filtered = [];

        foreach ($row as $col => $val) {
            try {
                if (Schema::hasColumn($table, $col) || Schema::hasColumn('dbo.' . $table, $col)) {
                    $filtered[$col] = $val;
                }
            } catch (\Throwable $e) {
                // kalau schema check error, mending coba insert aja (tapi amanin minimal)
                $filtered[$col] = $val;
            }
        }

        return $filtered;
    }
}
