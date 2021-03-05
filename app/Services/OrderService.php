<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Illuminate\Support\Facades\{DB, Log};
use Exception;
class OrderService
{
    public function update(OrderRequest $request)
    {
        DB::beginTransaction();
        try
        {
            $order = Order::where('id', $request->id)->first();
            $forUpdate = [
                'status'        => $request->status,        
                'profit_loss'   => $request->pl,
                'reason'        => $request->reason
            ];
            if ($order->update($forUpdate))
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Order successfully updated.',
                    'data'        => $order
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Updating order ' . $request->id . ' failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }
}
