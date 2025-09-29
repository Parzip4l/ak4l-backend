<?php

namespace App\Models\Rikes;

use Illuminate\Database\Eloquent\Model;

class RikesPradinas extends Model
{
    protected $fillable = [
        'periode', 'asp', 'occ', 'sarana', 'prasarana', 'target', 'keterangan'
    ];
}
