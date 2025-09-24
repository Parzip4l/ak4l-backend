<?php 

// app/Models/IncidentCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'visitor_name', 'visitor_company', 'purpose', 'visit_date',
        'host_id', 'status', 'notes'
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}

