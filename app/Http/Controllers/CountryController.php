<?php

namespace App\Http\Controllers;

use App\Exceptions\CountryServiceException;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function __construct(private readonly CountryService $countryService) {}

    public function index(): JsonResponse
    {
        try {
            $countries = $this->countryService->getCountries();

            return response()->json(['data' => $countries]);
        } catch (CountryServiceException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                503,
            );
        }
    }
}
