<?php

namespace App\Auth;

use App\Models\MUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\DB;

class MUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return MUser::query()->find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        try {
            return MUser::query()
                ->where((new MUser)->getAuthIdentifierName(), $identifier)
                ->where('remember_token', $token)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        try {
            DB::table('dbo.M_User')
                ->where('UserID', $user->getAuthIdentifier())
                ->update(['remember_token' => $token]);
        } catch (\Throwable $e) {
            // kolom gak ada? skip
        }
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $login = trim((string)($credentials['login_id'] ?? ''));
        if ($login === '') return null;

        // ✅ lookup via decrypt (karena EncryptByPassPhrase non-deterministic)
        $row = DB::selectOne("
            SELECT TOP 1
                UserID, Nama, Inisial, PosisiID, Versi, Role, Su
            FROM dbo.M_User
            WHERE dbo.fcDecrypt(LoginID) = ?
        ", [$login]);

        if (!$row) return null;

        return (new MUser)->newFromBuilder((array)$row);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $pass = (string)($credentials['password'] ?? '');
        if ($pass === '') return false;

        // ✅ compare via decrypt juga
        $row = DB::selectOne("
            SELECT CASE
                WHEN dbo.fcDecrypt(Passwordd) = ? THEN 1
                ELSE 0
            END AS ok
            FROM dbo.M_User
            WHERE UserID = ?
        ", [$pass, $user->getAuthIdentifier()]);

        return (int)($row->ok ?? 0) === 1;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // no-op
    }
}
