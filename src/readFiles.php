<?php

require_once 'models/customer.php';
require_once 'models/product.php';
require_once 'models/promotion.php';
require_once 'models/shippingZone.php';
require_once 'models/order.php';

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
        $level = $row[2] ?? 'BASIC';
        $currency = $row[4] ?? 'EUR';
        $shippingZone = $row[3] ?? 'ZONE1';
        $customers[$row[0]] = new Customer($row[0], $row[1], $level, $shippingZone, $currency);
    }
    return $customers;
}

function readProducts(string $path) : array {
    $lines = readCsv($path);
    $products = [];
    foreach($lines as $row) {
        $weight = floatval($row[4] ?? 1.0);
        $taxable = ($row[5] ?? 'true') === 'true';
        $products[$row[0]] = new Product($row[0], $row[1], $row[2], floatval($row[3]), $weight, $taxable);
    }
    return $products;
}

function readShippingZones(string $path) : array {
    $lines = readCsv($path);
    $shippingZones = [];
    foreach($lines as $row) {
        $perKg = floatval($row[2] ?? 0.5);
        $shippingZones[$row[0]] = new ShippingZone($row[0], floatval($row[1]), $perKg);
    }
    return $shippingZones;
}

function readPromotions(string $path) : array {
    $lines = readCsv($path);
    $promotions = [];
    foreach($lines as $row) {
        $active = ($row[3] ?? 'true') !== 'false';
        $promotions[$row[0]] = new Promotion($row[0], $row[1], $row[2], $active);
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
        $date = $row[5] ?? '';
        $promoCode = $row[6] ?? '';
        $time = $row[7] ?? '12:00';
        $customerId = $row[1];
        $orders[] = new Order($row[0], $customerId, $row[2], $qty, $price, $date, $promoCode, $time);
    }
    return $orders;
}