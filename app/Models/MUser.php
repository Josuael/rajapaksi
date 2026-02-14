<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'dbo.M_User';
    protected $primaryKey = 'UserID';
    public $timestamps = false;

    // Jangan biarin field varbinary kebawa JSON / debug / view
    protected $hidden = [
        'LoginID',
        'Passwordd',
    ];

    // Role web (kita tambahin), kalau gak ada kolomnya di DB, aman (akan null)
    protected $fillable = [
        'Nama',
        'Inisial',
        'PosisiID',
        'Versi',
        'Role',
        'Su',
        'remember_token',
    ];

    /**
     * Laravel auth identifier
     */
    public function getAuthIdentifierName(): string
    {
        return 'UserID';
    }

    /**
     * Laravel auth password field.
     * (kita gak pakai hashing Laravel, tapi biar contract terpenuhi)
     */
    public function getAuthPassword(): string
    {
        return ''; // kita validasi password via provider (fcEncrypt compare)
    }

    /**
     * Super penting:
     * Paksa buang varbinary dari output array biar json_encode gak error (Malformed UTF-8)
     */
    public function toArray(): array
    {
        $arr = parent::toArray();

        unset($arr['LoginID'], $arr['Passwordd']);

        return $arr;
    }

    /**
     * Optional helper: role fallback
     */
    public function getRoleAttribute($value)
    {
        return $value ?? 'karyawan';
    }
}
