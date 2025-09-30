<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
