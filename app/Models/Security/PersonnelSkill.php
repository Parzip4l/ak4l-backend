<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'skill_id',
        'certificate',
        'member_card',
        'certificate_file',
        'member_card_file'
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }
}
