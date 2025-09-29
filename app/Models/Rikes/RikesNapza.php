<?php

namespace App\Models\Rikes;

use Illuminate\Database\Eloquent\Model;

class RikesNapza extends Model
{
    protected $fillable = [
        'periode', 'passed', 'not_passed', 'kehadiran', 'target', 'keterangan'
    ];
}
