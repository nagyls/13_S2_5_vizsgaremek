<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    // Intézményszám lekérdezése régiónként
    private function getSchoolCountsByRegion(): \Illuminate\Support\Collection
    {
        return DB::table('establishments')
            ->join('settlements', 'settlements.id', '=', 'establishments.settlement_id')
            ->join('inner_regions', 'inner_regions.id', '=', 'settlements.inner_region_id')
            ->select('inner_regions.region_id', DB::raw('COUNT(establishments.id) as cnt'))
            ->groupBy('inner_regions.region_id')
            ->pluck('cnt', 'inner_regions.region_id');
    }

    // Régiók keresése névtöredék alapján
    public function regions(Request $request)
    {
        $query = Region::query();
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $regions = $query->orderBy('title')->get();

        $schoolCounts = $this->getSchoolCountsByRegion();

        $regions->each(function ($region) use ($schoolCounts) {
            $schoolsCount = 0;
            if (isset($schoolCounts[$region->id])) {
                $schoolsCount = (int) $schoolCounts[$region->id];
            }
            $region->schools_count = $schoolsCount;
        });

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    // Összes régió listázása
    public function getallregions()
    {
        $regions = Region::orderBy('title')->get();

        $schoolCounts = $this->getSchoolCountsByRegion();

        $regions->each(function ($region) use ($schoolCounts) {
            $schoolsCount = 0;
            if (isset($schoolCounts[$region->id])) {
                $schoolsCount = (int) $schoolCounts[$region->id];
            }
            $region->schools_count = $schoolsCount;
        });

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }
    // Egy régió adatainak lekérése ID alapján
    public function showregion($id)
    {
        $region = Region::find($id);

        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }


    // Almegyék keresése régió ID és/vagy névtöredék alapján
    public function innerregions(Request $request)
    {
        $query = InnerRegion::query();
        if ($request->has('search') && !empty($request->search) && $request->has('region_id') && !empty($request->region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('region_id', '=', "{$request->region_id}");
        } else if ($request->has('region_id') && !empty($request->region_id)) {

            $query->where('region_id', '=', "{$request->region_id}");
            $innerRegions = $query->orderBy('title')->get();
            
            return response()->json([
                'data' => $innerRegions
            ]);
        } else {
            return response()->json([
                'data' => []
            ]);
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'data' => $innerRegions
        ]);
    }
    // Összes almegye listázása
    public function getallinnerregions(Request $request)
    {
        $query = InnerRegion::query();

        if ($request->has('region_id') && !empty($request->region_id)) {
            $query->where('region_id', $request->region_id);
        }

        $innerRegions = $query->orderBy('title')->get();

        return response()->json([
            'data' => $innerRegions
        ]);
    }

    // Települések keresése almegye ID és/vagy névtöredék alapján
    public function settlements(Request $request)
    {
        $query = Settlement::query();
        if ($request->has('search') && !empty($request->search) && $request->has('inner_region_id') && !empty($request->inner_region_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('inner_region_id', '=', "{$request->inner_region_id}");
        } else if ($request->has('inner_region_id') && !empty($request->inner_region_id)) {

            $query->where('inner_region_id', '=', "{$request->inner_region_id}");
            $settlements = $query->orderBy('title')->get();
            
            return response()->json([
                'data' => $settlements
            ]);
        } else {
            return response()->json([
                'data' => []
            ]);
        }

        $settlements = $query->orderBy('title')->get();

        return response()->json([
            'data' => $settlements
        ]);
    }
    // Összes település listázása
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
