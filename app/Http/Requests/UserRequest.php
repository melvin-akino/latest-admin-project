<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\RequiredIf;
use App\Models\User;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = User::where('email', $this->input('email'))->first();
        $uniqueEmail = '';

        if ($user && ($user->id == $this->input('id'))) 
        {
            $uniqueEmail = ','.$this->input('id');
        }
        $update = !empty($user->id) ? ",$user->id" : ''; 
        return [
            'email'         => 'required|max:255|unique:users,email'.$uniqueEmail,
            'firstname'     => 'required',
            'lastname'      => 'required',
            'currency_id'   => new RequiredIf(!$this->input('id')),
            'balance'       => new RequiredIf(!$this->input('id')),
            'password'      => new RequiredIf(!$this->input('id'))
        ];
    }
}
