<?php 

// app/Models/IncidentCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function securityMetrics()
    {
        return $this->hasMany(SecurityMetric::class);
    }
}
