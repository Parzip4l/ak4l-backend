<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityKeyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'kasus_kriminal',
        'kasus_ancaman_bom',
        'kasus_huru_hara',
        'kasus_vandalisme',
        'kasus_lainnya',
        'inspeksi_pengamanan',
        'investigasi_insiden_pengamanan',
        'audit_internal_smp',
        'simulasi_tanggap_darurat_pengamanan',
        'rapat_koordinasi_3_pilar'
    ];

    protected $dates = ['month'];
}
