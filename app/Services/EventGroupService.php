<?php

namespace App\Services;

use App\Http\Requests\EventGroupRequest;
use Illuminate\Support\Facades\{DB, Log};
use App\Models\EventGroup;
use Exception;

class EventGroupService
{
    public static function match(EventGroupRequest $request)
    {
        DB::beginTransaction();
        try {
                $eventGroup = new EventGroup([
                    'master_event_id' => self::getMasterEventId($request->primary_provider_event_id),
                    'event_id'        => $request->match_event_id
                ]);

            if ($eventGroup->save())
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Event Group has been successfully added.',
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Creating event group for event id: ' . $request->match_event_id . ' has failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    private static function getMasterEventId($primaryProviderEventId) {
        return EventGroup::where('event_id', $primaryProviderEventId)->first()->master_event_id;
    }
}
