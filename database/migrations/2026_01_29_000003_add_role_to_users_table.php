<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 32)
                    ->default('karyawan')
                    ->after('email')
                    ->index();
            }
        });

        // Transition-safe: promote env admins to DB role so you don't get locked out.
        $admins = array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', ''))));
        if (!empty($admins)) {
            DB::table('users')
                ->whereIn('email', $admins)
                ->update(['role' => 'admin']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                // If index name differs on your DB, you can replace with $table->dropIndex('users_role_index');
                $table->dropIndex(['role']);
                $table->dropColumn('role');
            }
        });
    }
};
