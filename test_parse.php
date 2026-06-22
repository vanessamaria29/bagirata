<?php

$lines = [
    '1x Nasi Putih Rp 7.272',
    '1x Bakwan Jagung Rp 5.454',
    '1x Bunga Pepaya Kecombrang Rp 10.909',
    '1x Ayam Goreng Lengkuas Rp 25.454',
    'Total Rp 49.089',
];

$itemsFound = [];
$pendingName = '';

foreach ($lines as $line) {
    $cleanLine = trim($line);
    if (empty($cleanLine)) {
        continue;
    }

    // --- INTERCEPTOR PAJAK & SERVICE ---
    if (preg_match('/(tax|ppn|pb1)/i', $cleanLine)) {
        continue;
    }

    if (preg_match('/(service|charge)/i', $cleanLine)) {
        continue;
    }

    $ignoredWords = ['Total', 'Subtotal', 'Payment', 'BCA'];
    $isSystemLine = false;
    foreach ($ignoredWords as $word) {
        if (preg_match("/\b".preg_quote($word, '/')."\b/i", $cleanLine)) {
            $isSystemLine = true;
            break;
        }
    }
    if ($isSystemLine) {
        continue;
    }

    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*$/', $cleanLine, $priceMatches)) {
        $rawPrice = $priceMatches[1];
        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);

        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);

        if ($isDecimalMode) {
            $cleanPrice = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
        } else {
            $cleanPrice = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
        }

        if ($cleanPrice <= 0) {
            continue;
        }

        $pos = strrpos($cleanLine, $rawPrice);
        if ($pos !== false) {
            $remainingText = trim(substr($cleanLine, 0, $pos));
        } else {
            $remainingText = trim(str_replace($rawPrice, '', $cleanLine));
        }

        if (empty($remainingText) && ! empty($pendingName)) {
            $remainingText = $pendingName;
        }

        $qty = 1;
        if (preg_match('/^(\d+)\s*[xX]?\s*/', $remainingText, $qtyMatches)) {
            $qty = (int) $qtyMatches[1];
            $remainingText = trim(substr($remainingText, strlen($qtyMatches[0])));
        }

        $itemName = preg_replace('/(Rp|IDR|USD|SGD|JPY|S\$|\$|¥)/i', '', $remainingText);
        $itemName = trim(preg_replace('/[^a-zA-Z0-9\s.\-]/', '', $itemName));

        if (preg_match('/[a-zA-Z]/', $itemName)) {
            $itemsFound[] = [
                'qty' => $qty,
                'name' => trim(strtoupper($itemName)),
                'price' => $cleanPrice,
            ];
            $pendingName = '';
        } else {
            echo "Failed to match letters in itemName: '$itemName'\n";
        }
    } else {
        echo "Failed to match price regex on: $cleanLine\n";
    }
}
print_r($itemsFound);
