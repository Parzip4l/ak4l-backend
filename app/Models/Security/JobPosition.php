<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'skill_job_position');
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }
}
