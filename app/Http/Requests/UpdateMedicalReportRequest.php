<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:rikes,napza',
            'date' => 'sometimes|date',
            'file' => 'sometimes|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
