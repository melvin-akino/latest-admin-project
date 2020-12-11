<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use App\Http\Requests\SystemConfigurationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemConfigurationsController extends Controller
{

    public function index() 
    {
        $data = SystemConfiguration::getAll();

        return response()->json($data);
    }

    public function manage(SystemConfigurationRequest $request)
    {
        DB::beginTransaction();
        try 
        {
            if (!empty($request)) 
            {

                $data = $request->toArray();
                $config = SystemConfiguration::where('id', $data['id'])->first();
                
                if (!empty($config) && $config->update($data)) 
                {
                    DB::commit();
                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'data'        => 'success'
                    ], 200);
                }
            }
        }
        catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'        => false,
                'status_code'   => 500,
                'errors'        => $e->getMessage()
            ], 500);
        }
        
    }
}
