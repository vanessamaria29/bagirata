<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Get the raw rate from IDR to the specified currency.
     */
    public function getRateFromIdrTo($currency)
    {
        $currency = strtoupper($currency);
        if ($currency === 'IDR') {
            return 1.0;
        }

        try {
            $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/IDR');
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rates'][$currency])) {
                    return $data['rates'][$currency];
                }
            }
        } catch (\Exception $e) {
            // Fallback handled below
        }

        // Hardcoded fallbacks if API is offline
        $fallbacks = [
            'USD' => 0.000061,
            'SGD' => 0.000083,
            'JPY' => 0.0097,
            'IDR' => 1.0,
        ];

        return $fallbacks[$currency] ?? 1.0;
    }
}
