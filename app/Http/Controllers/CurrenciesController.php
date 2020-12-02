<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $data['data'][] = [
                'id'                => $currency['id'],
                'code'              => $currency['code'],
            ];
        }

        return response()->json(!empty($data) ? $data : []);
    }
}
