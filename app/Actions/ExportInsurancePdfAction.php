<?php

namespace App\Actions;

use App\Models\Insurance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportInsurancePdfAction
{
    public function execute(Insurance $insurance): Response
    {
        $pdf = Pdf::loadView('pdf.quote', ['insurance' => $insurance])
            ->setPaper('a4');

        return $pdf->download("cotizacion-{$insurance->id}.pdf");
    }
}