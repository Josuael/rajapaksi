<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MUserWeb extends Model
{
    protected $table = 'dbo.M_User_Web';
    protected $primaryKey = 'UserID';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'UserID', 'LoginName', 'Nama', 'Role'
    ];
}
