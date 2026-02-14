<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Injection extends Model
{
    protected $table = 'injection';

    public $timestamps = false;

    protected $fillable = [
        'id_bom',
        'po_customer',
        'art_code',
        'art_name',
        'size',
        'spk',
        'awal',
        'akhir',
        'belum_inject',
        'finish',
        'today',
        'balance',
    ];

    protected $casts = [
        'size' => 'integer',
        'spk' => 'integer',
        'awal' => 'date:Y-m-d',
        'akhir' => 'date:Y-m-d',
        'belum_inject' => 'integer',
        'finish' => 'integer',
        'today' => 'integer',
        'balance' => 'integer',
    ];
}
