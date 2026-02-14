<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    // ✅ list
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1) $perPage = 15;
        if ($perPage > 200) $perPage = 200;

        $page = (int) $request->query('page', 1);
        if ($page < 1) $page = 1;

        $offset = ($page - 1) * $perPage;

        // =========================
        // BASE SELECT (tanpa ORDER BY)
        // =========================
        $baseSql = "
            SELECT
                UserID,
                dbo.fcDecrypt(LoginID) AS LoginID_plain,
                Nama,
                Inisial,
                PosisiID,
                Versi,
                Role,
                Su
            FROM dbo.M_User
        ";

        $bindings = [];
        if ($q !== '') {
            $baseSql .= " WHERE (Nama LIKE ? OR PosisiID LIKE ? OR Role LIKE ? OR dbo.fcDecrypt(LoginID) LIKE ?)";
            $like = "%{$q}%";
            $bindings = [$like, $like, $like, $like];
        }

        // =========================
        // COUNT (tanpa ORDER BY!)
        // =========================
        $countSql = "SELECT COUNT(1) AS cnt FROM ({$baseSql}) x";
        $total = (int) (DB::selectOne($countSql, $bindings)->cnt ?? 0);

        // =========================
        // DATA (ORDER BY + OFFSET/FETCH)
        // =========================
        $dataSql = $baseSql . "
            ORDER BY UserID DESC
            OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
        ";

        $rows = DB::select($dataSql, array_merge($bindings, [$offset, $perPage]));

        // paginator object
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        // ✅ IMPORTANT: kirim variabel $users biar blade lu aman
        return view('admin.users.index', [
            'users' => $users,
            'q'     => $q,
        ]);
    }


    // ✅ create form
    public function create()
    {
        // posisi dropdown (ambil distinct biar gak berat)
        $posisiOptions = collect(DB::select("
            SELECT DISTINCT PosisiID
            FROM dbo.M_User
            WHERE PosisiID IS NOT NULL AND LTRIM(RTRIM(PosisiID)) <> ''
            ORDER BY PosisiID
        "))->pluck('PosisiID');

        return view('admin.users.create', [
            'posisiOptions' => $posisiOptions,
        ]);
    }

    // ✅ store
    public function store(Request $request)
    {
        $data = $request->validate([
            'login_id' => ['required','string','max:50'],
            'password' => ['required','string','min:3','max:50'],
            'nama'     => ['nullable','string','max:150'],
            'inisial'  => ['required','string','max:5'],
            'posisi_id'=> ['required','string','max:50'],
            'versi'    => ['nullable','string','max:10'],
            'role'     => ['required','in:admin,internal,karyawan'],
            'su'       => ['nullable','boolean'],
        ]);

        $versi = $data['versi'] ?: '1606.21.4';
        $su = !empty($data['su']) ? 1 : 0;

        // insert: LoginID & Passwordd via fcEncrypt
        DB::insert("
            INSERT INTO dbo.M_User (LoginID, Passwordd, Nama, Inisial, PosisiID, Versi, Role, Su)
            VALUES (dbo.fcEncrypt(?), dbo.fcEncrypt(?), ?, ?, ?, ?, ?, ?)
        ", [
            $data['login_id'],
            $data['password'],
            $data['nama'] ?? null,
            $data['inisial'],
            $data['posisi_id'],
            $versi,
            $data['role'],
            $su,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created.');
    }

    // ✅ edit form
    public function edit($id)
    {
        $user = DB::selectOne("
            SELECT
                UserID,
                dbo.fcDecrypt(LoginID) AS LoginPlain,
                Nama,
                Inisial,
                PosisiID,
                Versi,
                Role,
                Su
            FROM dbo.M_User
            WHERE UserID = ?
        ", [$id]);

        abort_if(!$user, 404);

        $posisiOptions = collect(DB::select("
            SELECT DISTINCT PosisiID
            FROM dbo.M_User
            WHERE PosisiID IS NOT NULL AND LTRIM(RTRIM(PosisiID)) <> ''
            ORDER BY PosisiID
        "))->pluck('PosisiID');

        return view('admin.users.edit', [
            'user' => $user,
            'posisiOptions' => $posisiOptions,
        ]);
    }

    // ✅ update (INI YANG BIKIN LU “reload di page yg sama” kalau salah)
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'login_id' => ['required','string','max:50'],
            'password' => ['nullable','string','min:3','max:50'], // optional
            'nama'     => ['nullable','string','max:150'],
            'inisial'  => ['required','string','max:5'],
            'posisi_id'=> ['required','string','max:50'],
            'versi'    => ['nullable','string','max:10'],
            'role'     => ['required','in:admin,internal,karyawan'],
            'su'       => ['nullable','boolean'],
        ]);

        $versi = $data['versi'] ?: '1606.21.4';
        $su = !empty($data['su']) ? 1 : 0;

        // update base fields
        DB::update("
            UPDATE dbo.M_User
            SET
                LoginID  = dbo.fcEncrypt(?),
                Nama     = ?,
                Inisial  = ?,
                PosisiID = ?,
                Versi    = ?,
                Role     = ?,
                Su       = ?
            WHERE UserID = ?
        ", [
            $data['login_id'],
            $data['nama'] ?? null,
            $data['inisial'],
            $data['posisi_id'],
            $versi,
            $data['role'],
            $su,
            $id,
        ]);

        // kalau password diisi, update juga
        if (!empty($data['password'])) {
            DB::update("
                UPDATE dbo.M_User
                SET Passwordd = dbo.fcEncrypt(?)
                WHERE UserID = ?
            ", [$data['password'], $id]);
        }

        // ✅ INI penting: redirect ke index, bukan balik ke edit
        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    // ✅ delete
    public function destroy($id)
    {
        DB::delete("DELETE FROM dbo.M_User WHERE UserID = ?", [$id]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
