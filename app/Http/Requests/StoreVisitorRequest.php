<?php 

// app/Http/Requests/StoreSecurityMetricRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'visitor_name'    => 'required|string|max:255',
            'visitor_company' => 'nullable|string|max:255',
            'purpose'         => 'required|string|max:500',
            'visit_date'      => 'required|date|after_or_equal:today',
            'host_id'         => 'required|exists:users,id',
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
