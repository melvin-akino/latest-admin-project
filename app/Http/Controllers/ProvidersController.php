<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProvidersController extends Controller
{
    public function index()
    {
        $providers = Provider::getAllProviders();
        if (!empty($providers)) {
            foreach ($providers as $provider) {
                $data['data'][] = [
                    'id'                => $provider['id'],
                    'alias'             => $provider['alias'],
                ];
            }
        }      

        return response()->json(!empty($data) ? $data : []);
    }
}
