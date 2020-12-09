<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class ProviderAccount extends Model
{
    protected $table = "provider_accounts";

    use SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'type',
        'punter_percentage',
        'provider_id',
        'is_enabled',
        'is_idle',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function providers()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public static function getProviderAccounts($providerId) 
    {
      $where = null;        
      if (!empty($providerId)) {
        $where = ['provider_id' => $providerId];
      }
        $accounts = self::where($where)
          ->join('providers as p', 'p.id', 'provider_id')
          ->select([
            'provider_accounts.id',
            'username',
            'password',
            'type',
            'provider_accounts.punter_percentage',
            'credits',
            'provider_accounts.is_enabled',
            'is_idle',
            'provider_id',
            'p.currency_id'
          ])                                    
          ->orderBy('id', 'DESC')
          ->get()
          ->toArray();

      $data = [];
        if (!empty($accounts)) {
            foreach($accounts as $account) {
                $data['data'][] = [
                    'id'                => $account['id'],
                    'username'          => $account['username'],
                    'password'          => $account['password'],
                    'type'              => $account['type'],
                    'punter_percentage' => $account['punter_percentage'],
                    'credits'           => $account['credits'],
                    'is_enabled'        => $account['is_enabled'],
                    'is_idle'           => $account['is_idle'],
                    'provider_id'       => $account['provider_id'],
                    'currency_id'       => $account['currency_id']
                ];
            }
        }
      return $data;
    }
}
