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
        $configs = SystemConfiguration::orderBy('id','asc')->get();
        foreach ($configs as $config) {
            $data['data'][] = [
                'id'                => $config['id'],
                'type'              => $config['type'],
                'value'             => $config['value'],
                'module'            => $config['module']
            ];
        }

        return response()->json(!empty($data) ? $data : null);
    }

    public function manage(SystemConfigurationRequest $request)
    {
        try 
        {
            if (!empty($request)) {

                $data = $request->toArray();
                $config = SystemConfiguration::where('id', $data['id'])->first();
                
                DB::beginTransaction();
                if ($config->update($data)) 
                {
                    DB::commit();
                    $message = 'success';

                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'data'        => $message
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
