<?php

namespace App\Http\Controllers;

use App\Models\GeneralErrorMessage;
use App\Http\Requests\GeneralErrorMessageRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GeneralErrorMessagesController extends Controller
{
    public function index()
    {
        $errorMessages = GeneralErrorMessage::getAll();      

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
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }
}