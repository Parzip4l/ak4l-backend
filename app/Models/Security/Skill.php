<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'reference',
        'criteria'
    ];

    public function jobPositions()
    {
        return $this->belongsToMany(JobPosition::class, 'skill_job_position');
    }

    public function personnelSkills()
    {
        return $this->hasMany(PersonnelSkill::class);
    }
}
