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
        $errorMessages = GeneralErrorMessage::all()->toArray();
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $error) {
                $data['data'][] = [
                    'id'                => $error['id'],
                    'error'             => $error['error']
                ];
            }
        }      

        return response()->json(!empty($data) ? $data : []);
    }

    public function manage(GeneralErrorMessageRequest $request) 
    {
        try {
            if (!empty($request)) {
                DB::beginTransaction();
                $data = $request->toArray();

                if (!empty($data['id'])) {
                    $error = GeneralErrorMessage::where('id', $data['id'])->first();
                    $error->id = $data['id'];
                    !empty($data['error']) ? $error->error = $data['error'] : null;               

                    if ($error->update($data)) {
                        $message = 'success';
                    }
                }
                else {
                    if (GeneralErrorMessage::create($data)) {
                        $message = 'success';
                    }                    
                }

                DB::commit();

                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'data'        => $message
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