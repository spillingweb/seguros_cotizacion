<?php

namespace App\Http\Controllers;

use App\Actions\CalculateQuoteAction;
use App\Actions\ContractInsuranceAction;
use App\Actions\CreateQuoteAction;
use App\Actions\ExportInsurancePdfAction;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\InsuranceResource;
use App\Models\Insurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class QuoteController extends Controller
{
    public function __construct(
        private readonly CalculateQuoteAction $calculateQuote,
        private readonly CreateQuoteAction $createQuote,
        private readonly ContractInsuranceAction $contractInsurance,
        private readonly ExportInsurancePdfAction $exportInsurancePdf,
    ) {}

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $result = $this->calculateQuote->execute(
            Carbon::parse($request->validated('departure_date')),
            Carbon::parse($request->validated('return_date')),
            $request->validated('destination_region'),
        );

        $insurance = $this->createQuote->execute($request->validated(), $result);

        return response()->json(new InsuranceResource($insurance), 201);
    }

    public function show(Insurance $insurance): JsonResponse
    {
        return response()->json(new InsuranceResource($insurance));
    }

    public function contract(Insurance $insurance): JsonResponse
    {
        if ($insurance->isContracted()) {
            return response()->json(
                ['message' => 'Este seguro ya fue contratado.'],
                422,
            );
        }

        $insurance = $this->contractInsurance->execute($insurance);

        return response()->json(new InsuranceResource($insurance->fresh()));
    }

    public function pdf(Insurance $insurance): Response
    {
        return $this->exportInsurancePdf->execute($insurance);
    }
}
