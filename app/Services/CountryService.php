<?php

namespace App\Services;

use App\Actions\CalculateQuoteAction;
use App\Enums\RegionSurcharge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    private const string CACHE_KEY = 'countries_list';
    private const int CACHE_TTL_SECONDS = 86400; // 24 horas
    private const int TIMEOUT_SECONDS = 10;
    private const string API_URL = 'https://countries.dev/countries';
    private const array REGION_LABELS = [
        'North America' => 'América del Norte',
        'South America' => 'América del Sur',
        'Europe' => 'Europa',
        'Asia' => 'Asia',
        'Africa' => 'África',
        'Oceania' => 'Oceanía',
    ];

    public function getCountries(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchFromApi();
        });
    }

    private function fetchFromApi(): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::API_URL, ['fields' => 'flags,region,translations,alpha2Code,subregion']);

            if ($response->serverError()) {
                throw new \RuntimeException('Error del servidor al obtener países.');
            }

            if ($response->failed()) {
                throw new \RuntimeException('No se pudo conectar con el servicio de países.');
            }

            $countries = $response->json();

            if (! is_array($countries)) {
                throw new \RuntimeException('Respuesta inesperada del servicio de países.');
            }

            return collect($countries)
                ->map(function (array $c) {
                    $region = $this->resolveRegion($c['region'] ?? '', $c['subregion'] ?? '');

                    return [
                        'name'                 => $c['translations']['es'] ?? 'Desconocido',
                        'code'                 => $c['alpha2Code'] ?? '',
                        'region'               => self::REGION_LABELS[$region] ?? '',
                        'flag'                 => $c['flags']['svg'] ?? $c['flags']['png'] ?? null,
                        'surcharge_percentage' => RegionSurcharge::rateFor($region) * 100,
                    ];
                })
                ->filter(fn (array $c) => $c['name'] && $c['code'] && $c['region'])
                ->sortBy('name')
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('CountryService: error inesperado', ['message' => $e->getMessage()]);
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    private function resolveRegion(string $region, string $subregion): string
    {
        if ($region === 'Americas') {
            $region = $subregion === 'South America' ? 'South America' : 'North America';
        }

        if (! array_key_exists($region, self::REGION_LABELS)) {
            return '';
        }

        return $region;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
