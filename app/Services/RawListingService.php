<?php

namespace App\Services;

use App\Models\{League, Team, Events};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Validator;

class RawListingService
{
    public function getByProvider(Request $request, string $type)
    {
        $types = [
            'league' => League::class,
            'team'   => Team::class,
            'event'  => Events::class
        ];

        $searchKey = '';
        $page = 1;
        $limit = 10;

        if ($request->has('searchKey')) $searchKey = $request->searchKey;

        if ($request->has('page')) $page = $request->page;

        if ($request->has('limit')) $limit = $request->limit;

        $data = $types[$type]::getByProvider($request->providerId, $searchKey, false);

        $result = [
            'total' => $data->count(),
            'pageNum' => $page,
            'pageData' => $data->skip(($page - 1) * $limit)->take($limit)->values()
        ];

        return response()->json($result);
    }
}
