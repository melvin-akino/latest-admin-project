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

        $toLogs = [
          "class"       => "SystemConfigurationsController",
          "message"     => $data,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

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

                    $toLogs = [
                      "class"       => "SystemConfigurationsController",
                      "message"     => [
                        'data'        => 'success'
                      ],
                      "module"      => "API",
                      "status_code" => 200
                    ];
                    monitorLog('monitor_api', 'info', $toLogs);

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

            $toLogs = [
              "class"       => "SystemConfigurationsController",
              "message"     => "Line " . $e->getLine() . " | " . $e->getMessage(),
              "module"      => "API_ERROR",
              "status_code" => $e->getCode()
            ];
            monitorLog('monitor_api', 'error', $toLogs);

            return response()->json([
                'status'        => false,
                'status_code'   => 500,
                'errors'        => $e->getMessage()
            ], 500);
        }
        
    }
}
