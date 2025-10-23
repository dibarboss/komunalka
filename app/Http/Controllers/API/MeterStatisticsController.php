<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Services\MeterStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterStatisticsController extends Controller
{
    public function __construct(private readonly MeterStatisticsService $statistics)
    {
    }

    public function show(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);

        return response()->json($this->statistics->calculate($meter));
    }

    private function ensureMeterBelongsToUser(Request $request, Meter $meter): Meter
    {
        abort_unless($meter->address && $meter->address->userHasAccess($request->user()), 404);

        return $meter;
    }
}
