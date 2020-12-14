<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderErrorMessageRequest;
use App\Models\ProviderErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderErrorMessagesController extends Controller
{
    public function index()
    {
        $providerErrorMessages = ProviderErrorMessage::getAll();

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
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }

   
}
