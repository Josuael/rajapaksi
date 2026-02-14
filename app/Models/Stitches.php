<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stitches extends Model
{
    protected $table = 'stitches';

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
        'finish',
        'today',
        'balance',
    ];

    protected $casts = [
        'size' => 'integer',
        'spk' => 'integer',
        'awal' => 'date:Y-m-d',
        'akhir' => 'date:Y-m-d',
        'finish' => 'integer',
        'today' => 'integer',
        'balance' => 'integer',
    ];
}
