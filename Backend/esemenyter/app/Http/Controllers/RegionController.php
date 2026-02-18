<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;

use Illuminate\Http\Request;

class RegionController extends Controller
{

    public function regions(Request $request)
    {
        $query = Region::query();
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $regions = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    public function getallregions()
    {
        $regions = Region::orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    public function showregion($id)
    {
        $region = Region::find($id);

        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }


    public function innerregions(Request $request)
    {
        $query = InnerRegion::query();
        if ($request->has('search') && !empty($request->search) && $request->has('region_id') && !empty($request->region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('region_id', '=', "{$request->region_id}");
        } else if ($request->has('region_id') && !empty($request->region_id)) {
            $innerRegions = $query->orderBy('title')->get();
            $query->where('region_id', '=', "{$request->region_id}");
            return response()->json([
                'success' => true,
                'data' => $innerRegions
            ]);
        } else {
            return response()->json([
                'data' => []
            ]);
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $innerRegions
        ]);
    }
    public function getallinnerregions(Request $request)
    {
        $query = InnerRegion::query();

        if ($request->has('region_id') && !empty($request->region_id)) {
            $query->where('region_id', $request->region_id);
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $innerRegions
        ]);
    }

    public function settlements(Request $request)
    {
        $query = Settlement::query();
        if ($request->has('search') && !empty($request->search) && $request->has('inner_region_id') && !empty($request->inner_region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('inner_region_id', '=', "{$request->inner_region_id}");
        } else if ($request->has('inner_region_id') && !empty($request->inner_region_id)) {
            $settlements = $query->orderBy('title')->get();
            $query->where('inner_region_id', '=', "{$request->inner_region_id}");
            return response()->json([
                'success' => true,
                'data' => $settlements
            ]);
        } else {
            return response()->json([
                'data' => []
            ]);
        }

        $settlements = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $settlements
        ]);
    }
    public function getallsettlements(Request $request)
    {
        $query = Settlement::query();

        if ($request->has('inner_region_id') && !empty($request->inner_region_id)) {
            $query->where('inner_region_id', $request->inner_region_id);
        }

        $settlements = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $settlements
        ]);
    }
}
