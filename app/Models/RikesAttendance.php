<?php 

// app/Models/RikesAttendance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RikesAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'division',
        'department',
        'date',
        'attendance_status',
        'result_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
