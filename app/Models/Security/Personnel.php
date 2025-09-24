<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bujp',
        'kta_number',
        'code',
        'job_position_id',
        'photo'
    ];

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function skills()
    {
        return $this->hasMany(PersonnelSkill::class);
    }
}
