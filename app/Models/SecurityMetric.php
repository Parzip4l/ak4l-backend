<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_category_id', 'title', 'description',
        'location', 'date', 'reported_by', 'status', 'approved_by'
    ];

    public function category()
    {
        return $this->belongsTo(IncidentCategory::class, 'incident_category_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
