<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Http\Requests\AdminUserRequest;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Http\Request;


class AdminUsersController extends Controller
{
    public function index()
    {
        $adminUsers = AdminUser::getAll();
        return response()->json($adminUsers);
    }
    public function manage (AdminUserRequest $request) 
    {
        DB::beginTransaction();
        try {
            if ($request->id) 
            {
                $adminUser = AdminUser::where('id', $request->id)->first();
                $adminUser->name = $request->name;
                $adminUser->status = $request->status;
                !empty($request->password) ? $adminUser->password = Hash::make($request->password) : null;
            }
            else 
            {
                $adminUser = new AdminUser([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'name' => $request->name,
                    'status' => $request->status
                ]);
            }

            if ($adminUser->save())
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'success',
                    'data'        => $adminUser
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }        
    }

    public function getAdminActivityLogs(Request $request)
    {
        $activityLogs = AdminUser::getActivityLogs($request->id);
        return response()->json($activityLogs);
    }

    public function getAdminUser($id)
    {
        $adminUser = AdminUser::getAdminUser($id);
        return response()->json($adminUser);
    }
}
