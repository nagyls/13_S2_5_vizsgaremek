<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\InnerRegion;
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
        $query = InnerRegion::query();
        if ($request->has('search') && !empty($request->search) && $request->has('region_id') && !empty($request->region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "{$search}%")->having('region_id', '=', "{$request->region_id}");
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
        $query = Settlements::query();
        if ($request->has('search') && !empty($request->search) && $request->has('region_id') && !empty($request->region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "{$search}%")->having('region_id', '=', "{$request->region_id}");
        }
        
        $innerRegions = $query->orderBy('title')->get();
        
        return response()->json([
            'success' => true,
            'data' => $innerRegions
        ]);
    }
    public function getallsettlements(Request $request)
    {
        $query = Settlements::query();

        if ($request->has('region_id') && !empty($request->region_id)) {
            $query->where('region_id', $request->region_id);
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $innerRegions
        ]);
    }
}