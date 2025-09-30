<?php 

// app/Models/BUJP/BujpReport.php
namespace App\Models\BUJP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BujpReport extends Model
{
    protected $fillable = [
        'type', 'month', 'submitted_by', 'file_path', 'status', 'notes'
    ];

    public function approvals(): HasMany
    {
        return $this->hasMany(BujpApproval::class, 'report_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
