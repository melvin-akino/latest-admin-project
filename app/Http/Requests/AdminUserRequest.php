<?php

namespace App\Http\Requests;

use App\Models\AdminUser;
use Illuminate\Foundation\Http\FormRequest;

class AdminUserRequest extends FormRequest
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
        
        $adminUser = AdminUser::where('email', $this->input('email'))->first();
        $update = '';       
        
        if (!empty($adminUser) && ($adminUser->id == $this->input('id'))) 
        {
            $update = ",$adminUser->id"; 
        }

        $required = '';
        if(!$this->input('id')) {
          $required = 'required';
        }

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admin_users,email'.$update,
            'password' => 'string|min:6|'.$required
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
