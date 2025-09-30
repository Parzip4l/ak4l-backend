<?php 

// app/Models/BUJP/BujpApproval.php
namespace App\Models\BUJP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BujpApproval extends Model
{
    protected $fillable = ['report_id', 'approved_by', 'action', 'notes'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(BujpReport::class, 'report_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
