<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'visitor_name'    => 'sometimes|string|max:255',
            'visitor_company' => 'nullable|string|max:255',
            'purpose'         => 'sometimes|string|max:500',
            'visit_date'      => 'sometimes|date|after_or_equal:today',
            'host_id'         => 'sometimes|exists:users,id',
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
