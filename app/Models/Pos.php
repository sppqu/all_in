<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pos extends Model
{
    protected $table = 'pos_pembayaran';
    protected $primaryKey = 'pos_id';
    public $timestamps = false;

    protected $fillable = [
        'pos_name',
        'pos_description',
    ];
} 