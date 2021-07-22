<?php

namespace App\Http\Requests;

use App\Models\ProviderAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProviderAccountRequest extends FormRequest
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
        $account = ProviderAccount::withTrashed()->where('username', $this->input('username'))->where('provider_id', $this->input('provider_id'));
        $uniqueUsername = "";

        if($account->exists()) {
            if(empty($this->input('id'))) {
                $uniqueUsername = "|unique:provider_accounts,username";
            } else {
                $account = $account->first();
                if($account->id != $this->input('id') && $account->username == $this->input('username') && $account->provider_id == $this->input('provider_id')) {
                    $uniqueUsername = "|unique:provider_accounts,username";
                }
            }
        }
        
        return [
            'line'              => 'required',
            'usage'             => 'required',
            'username'          => 'required|max:50'.$uniqueUsername,
            'password'          => 'required',
            'punter_percentage' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'username.unique' => 'A username with that provider already exists.'
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
