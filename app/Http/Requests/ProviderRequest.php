<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $path = $this->path();
        $rules['name']              = 'required|min:3';
        $rules['alias']             = 'required|max:3';
        $rules['punter_percentage'] = 'required|numeric';
        $rules['is_enabled']        = 'required';
        $rules['currency_id']       = 'required|integer';
        
        if ($path == 'api/providers/create') {
            $rules['name'] .= '|unique:providers,name';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'      => false,
            'status_code' => 400,
            'errors'      => $validator->errors(),
        ], 400);

        throw new ValidationException($validator, $response);
    }
}