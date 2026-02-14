<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoProcessSeeder extends Seeder
{
    public function run(): void
    {
        $max = 200;

        // === OPTIONAL: bersihin data biar gak duplicate ===
        // Kalau kamu gak mau hapus data existing, comment block ini.
        DB::statement("DELETE FROM finishing");
        DB::statement("DELETE FROM lasting");
        DB::statement("DELETE FROM injection");
        DB::statement("DELETE FROM strobel");
        DB::statement("DELETE FROM stitches");
        DB::statement("DELETE FROM bom");

        // Reset identity bom kalau ada (SQL Server)
        // Kalau error karena bukan identity, comment aja.
        try {
            DB::statement("DBCC CHECKIDENT ('bom', RESEED, 0)");
        } catch (\Throwable $e) {
            // ignore
        }

        $sizes = ['36','37','38','39','40','41','42','43','44','45'];
        $customers = ['NIKE','ADIDAS','PUMA','ASICS','NEW BALANCE','SKECHERS','LOCAL'];

        for ($i = 1; $i <= $max; $i++) {
            $bomCode = 'BOM-' . str_pad((string)$i, 5, '0', STR_PAD_LEFT);

            $awal  = Carbon::now()->subDays(rand(1, 30))->startOfDay();
            $akhir = (clone $awal)->addDays(rand(0, 5))->startOfDay();

            $qtySpk = rand(100, 1000);

            // === INSERT MASTER BOM ===
            $bomId = DB::table('bom')->insertGetId([
                'bom_id'      => $bomCode,
                'po_customer' => $customers[array_rand($customers)] . '-' . rand(1000, 9999),
                'art_code'    => 'ART-' . strtoupper(Str::random(4)),
                'art_name'    => 'Article ' . strtoupper(Str::random(6)),
                'size'        => $sizes[array_rand($sizes)],
                'qty_spk'     => $qtySpk,
                // kalau tabel bom kamu punya timestamps:
                // 'created_at'  => now(),
                // 'updated_at'  => now(),
            ]);

            // helper angka progress
            $today  = rand((int)($qtySpk * 0.2), $qtySpk);
            $finish = rand(0, $today);
            $balance = max(0, $today - $finish);

            // === STITCHES ===
            DB::table('stitches')->insert([
                'bom_id'  => $bomId,
                'awal'    => $awal->toDateString(),
                'akhir'   => $akhir->toDateString(),
                'finish'  => $finish,
                'today'   => $today,
                'balance' => $balance,
            ]);

            // === STROBEL ===
            $today2  = rand((int)($qtySpk * 0.2), $qtySpk);
            $finish2 = rand(0, $today2);
            DB::table('strobel')->insert([
                'bom_id'        => $bomId,
                'awal'          => $awal->toDateString(),
                'akhir'         => $akhir->toDateString(),
                'belum_strobel' => rand(0, $qtySpk),
                'finish'        => $finish2,
                'today'         => $today2,
                'balance'       => max(0, $today2 - $finish2),
            ]);

            // === INJECTION ===
            $today3  = rand((int)($qtySpk * 0.2), $qtySpk);
            $finish3 = rand(0, $today3);
            DB::table('injection')->insert([
                'bom_id'       => $bomId,
                'awal'         => $awal->toDateString(),
                'akhir'        => $akhir->toDateString(),
                'belum_inject' => rand(0, $qtySpk),
                'finish'       => $finish3,
                'today'        => $today3,
                'balance'      => max(0, $today3 - $finish3),
            ]);

            // === LASTING ===
            $today4  = rand((int)($qtySpk * 0.2), $qtySpk);
            $finish4 = rand(0, $today4);
            DB::table('lasting')->insert([
                'bom_id'        => $bomId,
                'awal'          => $awal->toDateString(),
                'akhir'         => $akhir->toDateString(),
                'belum_lasting' => rand(0, $qtySpk),
                'finish'        => $finish4,
                'today'         => $today4,
                'balance'       => max(0, $today4 - $finish4),
            ]);

            // === FINISHING ===
            $today5  = rand((int)($qtySpk * 0.2), $qtySpk);
            $finish5 = rand(0, $today5);
            DB::table('finishing')->insert([
                'bom_id'  => $bomId,
                'awal'    => $awal->toDateString(),
                'akhir'   => $akhir->toDateString(),
                'finish'  => $finish5,
                'today'   => $today5,
                'balance' => max(0, $today5 - $finish5),
            ]);
        }
    }
}
