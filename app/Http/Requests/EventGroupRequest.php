<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class EventGroupRequest extends FormRequest
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
        return [
            'primary_provider_event_id' => 'required|int|exists:event_groups,event_id',
            'match_event_id'            => 'required|int|check_if_league_and_team_is_matched'
        ];        
    }

    public function messages()
    {
        return [
            'match_event_id.check_if_league_and_team_is_matched' => 'The supplied :attribute has no valid matched leagues and teams.',
            'event_id.check_if_league_and_team_is_matched' => 'The supplied :attribute has no valid matched leagues and teams.'
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