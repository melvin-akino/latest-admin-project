<?php

namespace App\Http\Controllers;

use App\Models\GeneralErrorMessage;
use App\Http\Requests\GeneralErrorMessageRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class GeneralErrorMessagesController extends Controller
{
    public function index()
    {
        $errorMessages = GeneralErrorMessage::getAll();      

        $toLogs = [
          "class"       => "GeneralErrorMessagesController",
          "message"     => $errorMessages,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($errorMessages);
    }

    public function manage(GeneralErrorMessageRequest $request) 
    {
        DB::beginTransaction();
        try {
            if (!empty($request)) {
                if (!empty($request->id)) {
                    $error        = GeneralErrorMessage::where('id', $request->id)->first();
                    $error->error = $request->error;               
                    $error->update();
                    $data    = $error;
                    $message = 'success';
                }
                else {
                    $error   = GeneralErrorMessage::create($request->toArray());
                    $data    = GeneralErrorMessage::where('id', $error->id)->first();
                    $message = 'success';
                }

                DB::commit();
                
                $toLogs = [
                  "class"        => "GeneralErrorMessagesController",
                  "message"      => [
                    'message'     => $message,
                    'data'        => $data
                  ],
                  "module"       => "API",
                  "status_code"  => 200
                ];
                monitorLog('monitor_api', 'info', $toLogs);

                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => $message,
                    'data'        => $data
                ], 200);
            }            
        }  
        catch (Exception $e) {
            DB::rollBack();

            $toLogs = [
              "class"       => "GeneralErrorMessagesController",
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