<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderErrorMessageRequest;
use App\Models\ProviderErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProviderErrorMessagesController extends Controller
{
    public function index()
    {
        $providerErrorMessages = ProviderErrorMessage::getAll();

        $toLogs = [
          "class"       => "ProviderErrorMessagesController",
          "message"     => $providerErrorMessages,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($providerErrorMessages);
    }

    public function manage(ProviderErrorMessageRequest $request) 
    {
        DB::beginTransaction();
        try {
            if (!empty($request)) {
                if (!empty($request->id)) {
                    $error = ProviderErrorMessage::where('id', $request->id)->first();
                    $error->message = $request->message;               
                    $error->error_message_id = $request->error_message_id;
                    $error->odds_have_changed = $request->odds_have_changed;
                    $error->retry_type_id = $request->retry_type_id;
                    $error->save();
                    $data    = $error;
                    $message = 'success';
                }
                else {
                    $error   = ProviderErrorMessage::create($request->toArray());
                    $data    = ProviderErrorMessage::where('id', $error->id)->first();
                    $message = 'success';                 
                }

                DB::commit();
                
                $toLogs = [
                  "class"       => "ProviderErrorMessagesController",
                  "message"     => [
                    'messsage'    => $message,
                    'data'        => $data
                  ],
                  "module"      => "API",
                  "status_code" => 200
                ];
                monitorLog('monitor_api', 'info', $toLogs);

                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'messsage'    => $message,
                    'data'        => $data
                ], 200);
            }            
        }  
        catch (Exception $e) {
            DB::rollBack();

            $toLogs = [
              "class"       => "ProviderErrorMessagesController",
              "message"     => "Line " . $e->getLine() . " | " . $e->getMessage(),
              "module"      => "API_ERROR",
              "status_code" => $e->getCode()
            ];
            monitorLog('monitor_api', 'error', $toLogs);

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }

   
}
