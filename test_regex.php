<?php

$lines = [
    'Ramen 1,500 JPY',
    'Chicken 12.00 SGD',
    'Burger $10.50',
    'Nasi Goreng Rp 45.000',
    'Pajak 10% 4.500 JPY',
    'Service Charge 1.50 SGD',
];

foreach ($lines as $cleanLine) {
    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
        $rawPrice = $priceMatches[1];
        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);

        if ($isDecimalMode) {
            $cleanPrice = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
        } else {
            $cleanPrice = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
        }

        echo "Line: $cleanLine -> Raw: '$rawPrice' -> Clean: $cleanPrice (Decimal? ".($isDecimalMode ? 'Y' : 'N').")\n";
    } else {
        echo "Failed: $cleanLine\n";
    }
}
