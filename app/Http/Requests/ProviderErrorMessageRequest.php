<?php

namespace App\Http\Requests;

use App\Models\ProviderErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class ProviderErrorMessageRequest extends FormRequest
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
        $existingErrorMessage = ProviderErrorMessage::where('id', $this->input('id'))->first();

        $update = !empty($existingErrorMessage->id) ? ",$existingErrorMessage->id" : '';  
        $uniqueName = "|unique:provider_error_messages,message$update";

        $retryType = $this->input('retry_type_id') ? 'exists:retry_types,id' : '';

        return [
            'message'           => "required|min:2|max:255$uniqueName",
            'error_message_id'  => "required",
            'retry_type_id'     => $retryType,
            'odds_have_changed' => "required|boolean"
        ];
    }

    public function response(array $errors)
    {
        return response()->json([
            config('response.status') => config('response.type.error'),
            config('response.errors') => $errors
        ]);
    }
}
