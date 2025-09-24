<?php 

// app/Http/Requests/StoreSecurityMetricRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreSecurityMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'incident_category_id' => 'required|exists:incident_categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'location'    => 'nullable|string|max:255',
            'date'        => 'required|date|before_or_equal:today',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
