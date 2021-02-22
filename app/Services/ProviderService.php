<?php

namespace App\Services;

use App\Http\Requests\ProviderRequest;
use App\Models\Provider;
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class ProviderService
{
    public static function getAllProviders()
    {
        try {
        $providers = Provider::select(['name', 'alias', 'punter_percentage', 'is_enabled', 'currency_id'])
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'data'        => !empty($providers) ? $providers : null
        ], 200);
        }
        catch (Exception $e)
        {
            Log::info('Viewing providers failed.');
            Log::error($e->getMessage());
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    public static function getIdFromAlias(ProviderRequest $request)
    {
        $query = Provider::where('alias', strtoupper($request->alias));

        if ($query->exists()) {
            return $query->first()->id;
        }
    }

    public static function create (ProviderRequest $request)
    {
        DB::beginTransaction();
        try {
                $provider = new Provider([
                    'name'              => $request->name,
                    'alias'             => $request->alias,
                    'punter_percentage' => $request->punter_percentage,
                    'is_enabled'        => $request->is_enabled,
                    'currency_id'       => $request->currency_id
                ]);

                $provider->updated_at = null;

            if ($provider->save())
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Provider successfully added.',
                    'data'        => [
                        'name'              => $provider->name,
                        'alias'             => $provider->alias,
                        'punter_percentage' => $provider->punter_percentage,
                        'is_enabled'        => $provider->is_enabled,
                        'currency_id'       => $provider->currency_id,
                        'created_at'        => $provider->created_at
                    ]
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Creating provider ' . $request->name . ' failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    public function update(ProviderRequest $request)
    {
        DB::beginTransaction();
        try
        {
            $provider = Provider::where('name', $request->name)->first();
            $forUpdate = [
                'id'                => $provider->id,
                'name'              => $provider->name,        
                'alias'             => $request->alias,
                'punter_percentage' => $request->punter_percentage,
                'is_enabled'        => $request->is_enabled,
                'currency_id'       => $request->currency_id
            ];
            if ($provider->update($forUpdate))
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Provider successfully updated.'
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Updating provider ' . $request->name . ' failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }
}
