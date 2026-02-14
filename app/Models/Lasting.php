<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lasting extends Model
{
    protected $table = 'lasting';

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
        'belum_lasting',
        'finish',
        'today',
        'balance',
    ];

    protected $casts = [
        'size' => 'integer',
        'spk' => 'integer',
        'awal' => 'date:Y-m-d',
        'akhir' => 'date:Y-m-d',
        'belum_lasting' => 'integer',
        'finish' => 'integer',
        'today' => 'integer',
        'balance' => 'integer',
    ];
}
