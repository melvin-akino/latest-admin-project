<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthToken extends Model
{
    protected $table = 'oauth_access_tokens';
    
    public static function getLastLogin($userId)
    {
        return self::where('user_id', $userId)
            ->selectRaw('created_at as last_login_date')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
