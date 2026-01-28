<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{

    public function regions(Request $request)
    {
        $query = Region::query();
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "{$search}%");
        }
        
        $regions = $query->orderBy('title')->get();
        
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    public function getAllRegions()
    {
        $regions = Region::orderBy('title')->get();
        
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    public function showRegion($id)
    {
        $region = Region::find($id);
        
        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Region not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }


    public function subregions(Request $request)
    {
        $query = Region::query();
        if ($request->has('search') && !empty($request->search) && $request->has('region_id') && !empty($request->region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "{$search}%")->having('region_id', '=', "{$request->region_id}");
        }
        
        $regions = $query->orderBy('title')->get();
        
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    public function getAllInnerRegions(Request $request)
    {
        $query = InnerRegion::query();

        if ($request->has('region_id') && !empty($request->region_id)) {
            $query->where('region_id', $request->region_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'LIKE', "{$request->search}%");
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $innerRegions
        ]);
    }
}