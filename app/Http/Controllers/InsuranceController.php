<?php

namespace App\Http\Controllers;

use App\Http\Resources\InsuranceResource;
use App\Models\Insurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Insurance::query()->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('identification_number', 'like', "%{$search}%")
                    ->orWhere('destination_country', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        $insurances = $query->paginate(15);

        return response()->json(InsuranceResource::collection($insurances)->response()->getData(true));
    }

    public function show(Insurance $insurance): JsonResponse
    {
        return response()->json(new InsuranceResource($insurance));
    }
}
