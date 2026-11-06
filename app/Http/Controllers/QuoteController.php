<?php

namespace App\Http\Controllers;

use App\Actions\CalculateQuoteAction;
use App\Actions\CreateInsuranceAction;
use App\Enums\InsuranceStatus;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\InsuranceResource;
use App\Models\Insurance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class QuoteController extends Controller
{
    public function __construct(
        private readonly CalculateQuoteAction $calculateQuote,
        private readonly CreateInsuranceAction $createInsurance,
    ) {}

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $result = $this->calculateQuote->execute(
            Carbon::parse($request->validated('departure_date')),
            Carbon::parse($request->validated('return_date')),
            $request->validated('destination_region'),
        );

        $insurance = $this->createInsurance->execute($request->validated(), $result);

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

        $insurance->update([
            'status'        => InsuranceStatus::Contracted,
            'contracted_at' => now(),
        ]);

        return response()->json(new InsuranceResource($insurance->fresh()));
    }

    public function pdf(Insurance $insurance): Response
    {
        $pdf = Pdf::loadView('pdf.quote', ['insurance' => $insurance])
            ->setPaper('a4');

        return $pdf->download("cotizacion-{$insurance->id}.pdf");
    }
}
