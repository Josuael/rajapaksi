<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table mirror untuk web (indexable)
        if (!Schema::hasTable('M_User_Web')) {
            Schema::create('M_User_Web', function (Blueprint $table) {
                $table->unsignedInteger('UserID')->primary();     // sama dengan dbo.M_User.UserID
                $table->string('LoginName', 50)->unique();        // username plain (indexable)
                $table->string('Nama', 150)->nullable();
                $table->string('Role', 20)->nullable();           // admin/internal/karyawan
                $table->timestamps();
            });
        }

        // ===========================
        // Indexing: IF EXISTS DROP ELSE CREATE
        // ===========================

        // Unique index LoginName (kalau unique di schema udah bikin index, tetap aman)
        DB::statement("
            IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_M_User_Web_LoginName' AND object_id = OBJECT_ID('dbo.M_User_Web'))
                DROP INDEX UX_M_User_Web_LoginName ON dbo.M_User_Web;
            CREATE UNIQUE INDEX UX_M_User_Web_LoginName ON dbo.M_User_Web(LoginName);
        ");

        DB::statement("
            IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_M_User_Web_Role' AND object_id = OBJECT_ID('dbo.M_User_Web'))
                DROP INDEX IX_M_User_Web_Role ON dbo.M_User_Web;
            CREATE INDEX IX_M_User_Web_Role ON dbo.M_User_Web(Role);
        ");

        DB::statement("
            IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_M_User_Web_Nama' AND object_id = OBJECT_ID('dbo.M_User_Web'))
                DROP INDEX IX_M_User_Web_Nama ON dbo.M_User_Web;
            CREATE INDEX IX_M_User_Web_Nama ON dbo.M_User_Web(Nama);
        ");
    }

    public function down(): void
    {
        // drop indexes dulu biar aman
        try {
            DB::statement("IF EXISTS (SELECT 1 FROM sys.indexes WHERE name='UX_M_User_Web_LoginName' AND object_id=OBJECT_ID('dbo.M_User_Web')) DROP INDEX UX_M_User_Web_LoginName ON dbo.M_User_Web;");
            DB::statement("IF EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_M_User_Web_Role' AND object_id=OBJECT_ID('dbo.M_User_Web')) DROP INDEX IX_M_User_Web_Role ON dbo.M_User_Web;");
            DB::statement("IF EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_M_User_Web_Nama' AND object_id=OBJECT_ID('dbo.M_User_Web')) DROP INDEX IX_M_User_Web_Nama ON dbo.M_User_Web;");
        } catch (\Throwable $e) {}

        Schema::dropIfExists('M_User_Web');
    }
};
