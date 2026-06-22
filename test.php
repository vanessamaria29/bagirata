<?php

$lines = [
    '1x Nasi Putih Rp 7.272',
    '1x Bakwan Jagung Rp 5.454',
    '1x Bunga Pepaya Kecombrang Rp 10.909',
    '1x Ayam Goreng Lengkuas Rp 25.454',
    '2x T-Bone 500gr 1.000.000',
    'Sirloin Steak 550.000',
    'Orange Juice 48.000',
    'T-Bone 500gr -> Rp 1.000.000',
];
$itemsFound = [];
foreach ($lines as $line) {
    $cleanLine = trim($line);
    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*$/', $cleanLine, $priceMatches)) {
        $rawPrice = $priceMatches[1];
        $cleanPriceStr = preg_replace('/[^\d]/', '', str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice));
        $cleanPrice = (int) $cleanPriceStr;

        if ($cleanPrice < 100) {
            continue;
        }

        $pos = strrpos($cleanLine, $rawPrice);
        if ($pos !== false) {
            $remainingText = trim(substr($cleanLine, 0, $pos));
        } else {
            $remainingText = trim(str_replace($rawPrice, '', $cleanLine));
        }

        $qty = 1;
        if (preg_match('/^(\d+)\s*[xX]?\s*/', $remainingText, $qtyMatches)) {
            $qty = (int) $qtyMatches[1];
            $remainingText = trim(substr($remainingText, strlen($qtyMatches[0])));
        }

        $itemName = trim(preg_replace('/[^a-zA-Z0-9\s.\-]/', '', $remainingText));
        $itemName = trim(preg_replace('/\b(Rp|IDR)\b/i', '', $itemName));

        if (preg_match('/[a-zA-Z]/', $itemName)) {
            $itemsFound[] = [
                'qty' => $qty,
                'name' => trim(strtoupper($itemName)),
                'price' => $cleanPrice,
            ];
        } else {
            echo "Failed name: $itemName\n";
        }
    } else {
        echo "Failed price match: $cleanLine\n";
    }
}
print_r($itemsFound);
