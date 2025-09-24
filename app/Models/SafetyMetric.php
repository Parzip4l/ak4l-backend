<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafetyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'fatality',
        'lost_time_injuries',
        'illness',
        'medical_treatment_cases',
        'first_aid_cases',
        'property_damage',
        'near_miss',
        'unsafe_action',
        'unsafe_condition',
        'work_hours',
        'lost_days',
        'far',
        'sr',
        'fr',
        'safety_inspection',
        'emergency_drill',
        'incident_investigation',
        'internal_audit',
        'p2k3_meeting',
        'safety_awareness',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
