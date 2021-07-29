<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Currency extends Model
{
    protected $table = "currency";
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'name',
        'code',
        'symbol'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function getAll()
    {
        $currencies = self::all();
        foreach ($currencies as $currency) {
            $data['data'][] = [
                'id'    => $currency['id'],
                'code'  => $currency['code'],
            ];
        }

        return !empty($data) ? $data : [];
    }

    public static function getIdByCode(string $code)
    {
        $query = self::where('code', $code);

        if ($query->count() == 0) {
            return false;
        }

        return $query->first()->id;
    }

    public static function getCodeById(int $id)
    {
        $query = self::where('id', $id);

        if ($query->count() == 0) {
            return false;
        }

        return $query->first()->code;
    }

    public function exchange_rate()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public static function getCurrenciesForConversion()
    {
        return DB::table('currency AS cfrom')
            ->join('currency AS cto', function ($join) {
                $join->on('cfrom.id', '=', 'cto.id');
                $join->orOn('cfrom.id', '!=', 'cto.id');
            })
            ->orderBy('cfrom.id', 'asc')
            ->orderBy('cto.id', 'asc')
            ->get([
                'cfrom.id AS from_id',
                'cfrom.code AS from_code',
                'cto.id AS to_id',
                'cto.code AS to_code'
            ]);
    }
}
