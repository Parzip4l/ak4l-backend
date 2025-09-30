<?php

namespace App\Models\MedicalOnsite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MedicalOnsiteApproval extends Model
{
    protected $fillable = ['report_id', 'approved_by', 'status', 'notes'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(MedicalOnsiteReport::class, 'report_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
