<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Get exchange rates from free open API with robust fallback.
     */
    public function getExchangeRates($base = 'IDR')
    {
        try {
            $response = Http::timeout(3)->get("https://open.er-api.com/v6/latest/{$base}");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback handled below
        }

        return null;
    }

    /**
     * Get the rate to convert 1 unit of foreign currency to IDR.
     */
    public function getRateFromIdrTo($currency)
    {
        $currency = strtoupper($currency);
        if ($currency === 'IDR') {
            return 1.0;
        }

        $rates = $this->getExchangeRates('IDR');
        if ($rates && isset($rates['rates'][$currency])) {
            $rateToCurrency = $rates['rates'][$currency];
            if ($rateToCurrency > 0) {
                return 1.0 / $rateToCurrency;
            }
        }

        // Hardcoded fallbacks if API is offline
        $fallbacks = [
            'USD' => 16400.0,
            'SGD' => 12100.0,
            'JPY' => 104.0,
            'IDR' => 1.0,
        ];

        return $fallbacks[$currency] ?? 1.0;
    }
}
