<?php

$lines = [
    'BreadTalk Japan Shinjuku',
    '1 POS1 レジ担当 : TAKASHI Y.',
    '伝票番号 : 3059689',
    '2019年5月10日 16:32:47',
    '1 特上寿司 4,500',
    '1 マグロ握り 2,800',
    '1 サーモンロール 1,200',
    '1 玉子焼き 800',
    '小計 : 9,300',
    '消費税 : 930',
    '合計 : 10,230',
    'お支払い : 10,230',
    'カード(JCB) : 10,230',
];

$itemsFound = [];
$pendingName = '';
$taxFound = 0;
$serviceFound = 0;

$ignoredWords = [
    'Total', 'Subtotal', 'Amount', 'Net', 'Jml', 'Bayar', 'Cash', 'Change', 'Kembali',
    'Tunai', 'Debit', 'Credit', 'Visa', 'Master', 'Card', 'Tax', 'Ppn', 'Pb1', 'Service',
    'Harga', 'Price', 'Qty', 'Item', 'Shift', 'Pos', 'No', 'Check', 'Bill', 'Order',
    'Table', 'Meja', 'Trans', 'Ref', 'Auth', 'Telp', 'Fax', 'Call', 'Jl', 'Jalan',
    'Thank', 'Terima', 'Kasih', 'Welcome', 'Selamat', 'Datang', 'Operator', 'Kasir', 'Cashier',
    'Disc', 'Diskon', 'Tanggal', 'Date', 'Waktu', 'Time', 'Jam', 'taxable', 'Payment', 'Pembayaran',
    'BCA', 'Mandiri', 'BNI', 'BRI', 'Gopay', 'OVO', 'Dana', 'LinkAja', 'QRIS',
    '小計', '合計', 'お支払い', 'カード', 'お釣り', 'レシート',
];

foreach ($lines as $line) {
    $cleanLine = trim($line);
    if (empty($cleanLine)) {
        continue;
    }

    // --- INTERCEPTOR PAJAK & SERVICE ---
    if (preg_match('/(tax|ppn|pb1|消費税)/i', $cleanLine)) {
        if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
            $rawPrice = $priceMatches[1];
            $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
            $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);
            if ($isDecimalMode) {
                $taxFound = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
            } else {
                $taxFound = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
            }
        }

        continue;
    }

    if (preg_match('/(service|charge)/i', $cleanLine)) {
        continue;
    }

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

    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
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
        $itemName = trim(preg_replace('/[^\p{L}0-9\s.\-]/u', '', $itemName));

        if (preg_match('/[\p{L}]/u', $itemName)) {
            $itemsFound[] = [
                'qty' => $qty,
                'name' => trim(mb_strtoupper($itemName, 'UTF-8')),
                'price' => $cleanPrice,
            ];
            $pendingName = '';
        } else {
            echo "Failed letters match: $itemName\n";
        }
    } else {
        echo "Failed price match: $cleanLine\n";
    }
}
print_r([
    'items' => $itemsFound,
    'tax' => $taxFound,
]);
