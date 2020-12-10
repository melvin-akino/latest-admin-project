<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{
    public function index()
    {
        $currencies = Currency::getAll();

        return response()->json($currencies);
    }
}
