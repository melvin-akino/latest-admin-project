<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class LeagueRequest extends FormRequest
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
        if ($path == 'api/leagues/unmatch') {
            return [
                'league_id'   => 'required|int|check_if_league_is_matched',
                'provider_id' => 'required|int|check_if_provider_is_secondary',
                'sport_id'    => 'required'
            ];
        }        
    }

    public function messages()
    {
        return [
            'league_id.check_if_league_is_matched'       => 'The supplied :attribute has no valid matched leagues.',
            'provider_id.check_if_provider_is_secondary' => 'The supplied :attribute is must not be from Primary.'
        ];
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