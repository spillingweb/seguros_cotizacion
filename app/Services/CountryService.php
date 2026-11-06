<?php

namespace App\Services;

use App\Actions\CalculateQuoteAction;
use App\Exceptions\CountryServiceException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    private const string CACHE_KEY = 'countries_list';
    private const int CACHE_TTL_SECONDS = 86400; // 24 horas
    private const int TIMEOUT_SECONDS = 10;
    private const string API_URL = 'https://countries.dev/countries';

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
                ->get(self::API_URL, ['fields' => 'flags,region,translations,alpha2Code']);

            if ($response->serverError()) {
                throw new CountryServiceException('Error del servidor al obtener países.');
            }

            if ($response->failed()) {
                throw new CountryServiceException('No se pudo conectar con el servicio de países.');
            }

            $countries = $response->json();

            if (! is_array($countries)) {
                throw new CountryServiceException('Respuesta inesperada del servicio de países.');
            }

            return collect($countries)
                ->map(fn (array $c) => [
                    'name'                 => $c['translations']['es'] ?? 'Desconocido',
                    'code'                 => $c['alpha2Code'] ?? '',
                    'region'               => $c['region'] ?? '',
                    'flag'                 => $c['flags']['svg'] ?? $c['flags']['png'] ?? null,
                    'surcharge_percentage' => CalculateQuoteAction::getSurchargeRateByRegion($c['region'] ?? '') * 100,
                ])
                ->filter(fn (array $c) => $c['name'] && $c['code'] && $c['region'])
                ->sortBy('name')
                ->values()
                ->toArray();

        } catch (CountryServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('CountryService: error inesperado', ['message' => $e->getMessage()]);
            throw new CountryServiceException('Error inesperado al obtener los países.', previous: $e);
        }
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
