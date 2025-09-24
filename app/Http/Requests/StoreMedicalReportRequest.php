<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bolehkan hanya user login
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:rikes,napza',
            'date' => 'required|date',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
