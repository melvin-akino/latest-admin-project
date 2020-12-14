<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\RequiredIf;

class UserRequest extends FormRequest
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

    public function rules()
    {

        $user = User::where('email', $this->input('email'))->first();
        $uniqueEmail = "|unique:users,email";
        if (!empty($user) && !empty($this->input('id')) && ($user->id == $this->input('id'))) {
            $uniqueEmail = "|unique:users,email,$user->id";
        }      
        
        return [
            'email'     => 'required|max:255'.$uniqueEmail,
            'firstname' => 'required',
            'lastname'  => 'required',
            'password'  =>  new RequiredIf(!$this->input('id'))
        ];

    }
}
