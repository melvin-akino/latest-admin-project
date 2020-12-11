<?php

namespace App\Http\Requests;

use App\Models\GeneralErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class GeneralErrorMessageRequest extends FormRequest
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
        
        $existingErrorMessage = GeneralErrorMessage::where('id', $this->input('id'))->first();

        $update = !empty($existingErrorMessage->id) ? ",$existingErrorMessage->id" : ''; 
        
        $uniqueName = "|unique:error_messages,error$update";

        return [
            'error'   => "required|min:2|max:255$uniqueName",
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
