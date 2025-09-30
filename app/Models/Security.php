<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = ['name', 'category', 'criteria', 'reference'];

    public function personnelSkills(): HasMany
    {
        return $this->hasMany(PersonnelSkill::class);
    }
}

class Personnel extends Model
{
    protected $fillable = ['name', 'job_position', 'bujp_name', 'kta_number', 'code'];

    public function skills(): HasMany
    {
        return $this->hasMany(PersonnelSkill::class);
    }
}

class PersonnelSkill extends Model
{
    protected $fillable = ['personnel_id', 'skill_id', 'certificate_file', 'membership_card', 'status'];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PersonnelSkillLog::class);
    }
}

class PersonnelSkillLog extends Model
{
    protected $fillable = ['personnel_skill_id', 'approved_by', 'status', 'notes'];

    public function personnelSkill(): BelongsTo
    {
        return $this->belongsTo(PersonnelSkill::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
