<?php

//lecture csv + parsing
function readCsv(string $path) : array {
    $result = [];
    if (($handle = fopen($path, 'r')) !== false) {
        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $result[] = $row;
        }
        fclose($handle);
    }
    return $result;
}

function readCustomers(string $path) : array {
    $lines = readCsv($path);
    $customers = [];
    foreach($lines as $row) {
        $customers[$row[0]] = [
            'id' => $row[0],
            'name' => $row[1],
            'level' => $row[2] ?? 'BASIC',
            'shipping_zone' => $row[3] ?? 'ZONE1',
            'currency' => $row[4] ?? 'EUR'
        ];
    }
    return $customers;
}

function readProducts(string $path) : array {
    $lines = readCsv($path);
    $products = [];
    foreach($lines as $row) {
        $products[$row[0]] = [
            'id' => $row[0],
            'name' => $row[1],
            'category' => $row[2],
            'price' => floatval($row[3]),
            'weight' => floatval($row[4] ?? 1.0),
            'taxable' => ($row[5] ?? 'true') === 'true'
        ];
    }
    return $products;
}

function readShippingZones(string $path) : array {
    $lines = readCsv($path);
    $shippingZones = [];
    foreach($lines as $row) {
        $shippingZones[$row[0]] = [
            'zone' => $row[0],
            'base' => floatval($row[1]),
            'per_kg' => floatval($row[2] ?? 0.5)
        ];
    }
    return $shippingZones;
}

function readPromotions(string $path) : array {
    $lines = readCsv($path);
    $promotions = [];
    foreach($lines as $row) {
        $promotions[$row[0]] = [
            'code' => $row[0],
            'type' => $row[1],
            'value' => $row[2],
            'active' => ($row[3] ?? 'true') !== 'false'
        ];
    }
    return $promotions;
}

function readOrders(string $path) : array {
    $lines = readCsv($path);
    $orders = [];
    foreach($lines as $row) {
        $qty = intval($row[3]);
        $price = floatval($row[4]);

        if ($qty <= 0 || $price < 0) {
            continue; // validation silencieuse
        }

        $orders[] = [
            'id' => $row[0],
            'customer_id' => $row[1],
            'product_id' => $row[2],
            'qty' => $qty,
            'unit_price' => $price,
            'date' => $row[5] ?? '',
            'promo_code' => $row[6] ?? '',
            'time' => $row[7] ?? '12:00'
        ];
    }
    return $orders;
}