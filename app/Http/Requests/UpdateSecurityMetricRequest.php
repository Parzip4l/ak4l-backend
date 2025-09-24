<?php 

// app/Http/Requests/UpdateSecurityMetricRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecurityMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'incident_category_id' => 'sometimes|exists:incident_categories,id',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'location'    => 'nullable|string|max:255',
            'date'        => 'sometimes|date|before_or_equal:today',
            'status'      => 'sometimes|in:pending,approved,rejected',
        ];
    }
}
