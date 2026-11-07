<?php

namespace App\Http\Controllers;

use App\Http\Resources\InsuranceResource;
use App\Models\Insurance;
use App\Services\InsuranceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function __construct(private readonly InsuranceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $insurances = $this->service->paginate($request);

        return response()->json(InsuranceResource::collection($insurances)->response()->getData(true));
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->service->stats());
    }

    public function show(Insurance $insurance): JsonResponse
    {
        return response()->json(new InsuranceResource($insurance));
    }
}
