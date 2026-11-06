<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

    .header { background: #1a56db; color: #fff; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { font-size: 22px; font-weight: 700; letter-spacing: 0.5px; }
    .header .subtitle { font-size: 11px; opacity: 0.85; margin-top: 4px; }
    .badge { background: rgba(255,255,255,0.2); border-radius: 4px; padding: 4px 12px; font-size: 11px; font-weight: 600; }

    .body { padding: 28px 32px; }

    .doc-meta { display: flex; justify-content: space-between; margin-bottom: 24px; }
    .doc-meta span { font-size: 11px; color: #555; }
    .doc-meta strong { color: #1a1a2e; }

    .section { margin-bottom: 22px; }
    .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1a56db; letter-spacing: 0.8px; border-bottom: 1.5px solid #e8f0fe; padding-bottom: 6px; margin-bottom: 12px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; }
    .field label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
    .field p { font-size: 12px; font-weight: 600; margin-top: 2px; }

    .pricing-box { background: #f0f5ff; border: 1px solid #c3d9fb; border-radius: 8px; padding: 16px 20px; }
    .pricing-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #dde8fb; }
    .pricing-row:last-child { border-bottom: none; }
    .pricing-row.total { font-weight: 700; font-size: 14px; color: #1a56db; margin-top: 6px; padding-top: 10px; border-top: 2px solid #1a56db; border-bottom: none; }

    .status-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-quoted { background: #fef3c7; color: #92400e; }
    .status-contracted { background: #d1fae5; color: #065f46; }

    .footer { margin-top: 32px; padding: 16px 32px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #999; }
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>SEGURO DE VIAJE</h1>
        <div class="subtitle">Cotización #{{ str_pad($insurance->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>
    <div class="badge">{{ $insurance->status->label() }}</div>
</div>

<div class="body">

    <div class="doc-meta">
        <span>Fecha de emisión: <strong>{{ now()->format('d/m/Y') }}</strong></span>
        @if($insurance->contracted_at)
        <span>Contratado el: <strong>{{ $insurance->contracted_at->format('d/m/Y H:i') }}</strong></span>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Datos del Asegurado</div>
        <div class="grid-2">
            <div class="field"><label>Nombre completo</label><p>{{ $insurance->full_name }}</p></div>
            <div class="field"><label>Identificación</label><p>{{ $insurance->identification_number }}</p></div>
            <div class="field"><label>Correo electrónico</label><p>{{ $insurance->email }}</p></div>
            <div class="field"><label>Fecha de nacimiento</label><p>{{ $insurance->date_of_birth->format('d/m/Y') }}</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Viaje</div>
        <div class="grid-2">
            <div class="field"><label>País de destino</label><p>{{ $insurance->destination_country }}</p></div>
            <div class="field"><label>Región</label><p>{{ $insurance->destination_region }}</p></div>
            <div class="field"><label>Fecha de salida</label><p>{{ $insurance->departure_date->format('d/m/Y') }}</p></div>
            <div class="field"><label>Fecha de regreso</label><p>{{ $insurance->return_date->format('d/m/Y') }}</p></div>
            <div class="field"><label>Duración del viaje</label><p>{{ $insurance->days_count }} días</p></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalle de la Cotización</div>
        <div class="pricing-box">
            <div class="pricing-row">
                <span>Tarifa base ({{ $insurance->days_count }} días × $3.00)</span>
                <span>USD {{ number_format($insurance->base_rate, 2) }}</span>
            </div>
            <div class="pricing-row">
                <span>Recargo por región {{ $insurance->destination_region }} ({{ $insurance->surcharge_percentage }}%)</span>
                <span>USD {{ number_format($insurance->base_rate * $insurance->surcharge_percentage / 100, 2) }}</span>
            </div>
            <div class="pricing-row total">
                <span>TOTAL A PAGAR</span>
                <span>USD {{ number_format($insurance->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Estado</div>
        <span class="status-badge status-{{ $insurance->status->value }}">
            {{ $insurance->status->label() }}
        </span>
    </div>

</div>

<div class="footer">
    Este documento es una cotización generada automáticamente. · Seguro de Viaje © {{ date('Y') }}
</div>

</body>
</html>
