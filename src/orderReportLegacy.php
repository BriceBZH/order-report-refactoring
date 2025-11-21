<?php

require_once 'readFiles.php';
require_once 'discountCalculator.php';
require_once 'config/config.php';

// Fonction principale qui fait TOUT (250+ lignes)
function run()
{
    $base = __DIR__;
    $custPath = $base . '/data/customers.csv';
    $ordPath = $base . '/data/orders.csv';
    $prodPath = $base . '/data/products.csv';
    $shipPath = $base . '/data/shipping_zones.csv';
    $promoPath = $base . '/data/promotions.csv';

    // Lecture customers
    $customers = readCustomers($custPath);

    // Lecture products 
    $products = readProducts($prodPath);

    // Lecture shipping zones 
    $shippingZones = readShippingZones($shipPath);

    // Lecture promotions
    $promotions = readPromotions($promoPath);

    // Lecture orders
    $orders = readOrders($ordPath);

    // Calcul points de fidélité (première duplication)
    $loyaltyPoints = [];
    foreach ($orders as $o) {
        $cid = $o->getCustomerId();
        if (!isset($loyaltyPoints[$cid])) {
            $loyaltyPoints[$cid] = 0;
        }
        // Calcul basé sur prix commande
        $loyaltyPoints[$cid] += $o->getQty() * $o->getUnitPrice() * Config::LOYALTY_RATIO;
    }

    // Groupement par client (logique métier mélangée)
    $totalsByCustomer = [];
    foreach ($orders as $o) {
        $cid = $o->getCustomerId();

        // Récupération produit avec fallback
        $prod = $products[$o->getProductId()] ?? [];
        $basePrice = $prod->getPrice() ?? $o->getUnitPrice();

        // Application promo (logique complexe et bugguée)
        $promoCode = $o->getPromoCode();
        $discountRate = 0;
        $fixedDiscount = 0;

        if (!empty($promoCode) && isset($promotions[$promoCode])) {
            $promo = $promotions[$promoCode];
            if ($promo->getActive()) {
                if ($promo->getType() === 'PERCENTAGE') {
                    $discountRate = floatval($promo->getValue()) / 100;
                } elseif ($promo->getType() === 'FIXED') {
                    // Bug: appliqué par ligne au lieu de global
                    $fixedDiscount = floatval($promo->getValue());
                }
            }
        }

        // Calcul ligne avec réduction promo
        $lineTotal = $o->getQty() * $basePrice * (1 - $discountRate) - $fixedDiscount * $o->getQty();

        // Bonus matin (règle cachée basée sur heure)
        $hour = intval(explode(':', $o->getTime())[0]);
        $morningBonus = 0;
        if ($hour < 10) {
            $morningBonus = $lineTotal * 0.03; // 3% réduction supplémentaire
        }
        $lineTotal = $lineTotal - $morningBonus;

        if (!isset($totalsByCustomer[$cid])) {
            $totalsByCustomer[$cid] = [
                'subtotal' => 0.0,
                'items' => [],
                'weight' => 0.0,
                'promoDiscount' => 0.0,
                'morningBonus' => 0.0
            ];
        }

        $totalsByCustomer[$cid]['subtotal'] += $lineTotal;
        $totalsByCustomer[$cid]['weight'] += ($prod->getWeight() ?? 1.0) * $o->getQty();
        $totalsByCustomer[$cid]['items'][] = $o;
        $totalsByCustomer[$cid]['morningBonus'] += $morningBonus;
    }

    // Génération rapport (mélange calculs + formatage + I/O)
    $outputLines = [];
    $jsonData = [];
    $grandTotal = 0.0;
    $totalTaxCollected = 0.0;

    // Tri par ID client (comportement à préserver)
    $sortedCustomerIds = array_keys($totalsByCustomer);
    sort($sortedCustomerIds);

    // var_dump($sortedCustomerIds);
    foreach ($sortedCustomerIds as $cid) {
        $cust = $customers[$cid] ?? [];
        $name = $cust->getName() ?? 'Unknown';
        $level = $cust->getLevel() ?? 'BASIC';
        $zone = $cust->getShippingZone() ?? 'ZONE1';
        $currency = $cust->getCurrency() ?? 'EUR';

        $sub = $totalsByCustomer[$cid]['subtotal'];

        // Remise par paliers
        $discountCalculator = new DiscountCalculator();
        $disc = $discountCalculator->discountStage($sub, $level);

        // Bonus weekend (règle cachée basée sur date)
        $firstOrderDate = $totalsByCustomer[$cid]['items'][0]->getDate() ?? '';
        $disc = $discountCalculator->discountWeekend($firstOrderDate, $disc);

        // Calcul remise fidélité (duplication)
        $pts = $loyaltyPoints[$cid] ?? 0;
        $loyaltyDiscount = $discountCalculator->discountFidelity($pts);

        // Plafond remise global (règle cachée)
        $finalDiscount = $discountCalculator->maxDiscount(Config::MAX_DISCOUNT, $disc, $loyaltyDiscount);

        // Calcul taxe (gestion spéciale par produit)
        $taxable = $sub - $finalDiscount['totalDiscount'];
        $tax = 0.0;

        // Vérifier si tous produits taxables
        $allTaxable = true;
        foreach ($totalsByCustomer[$cid]['items'] as $item) {
            $prod = $products[$item->getProductId()] ?? null;
            if ($prod && $prod->getTaxable() === false) {
                $allTaxable = false;
                break;
            }
        }

        if ($allTaxable) {
            $tax = round($taxable * Config::TAX, 2); // Arrondi 2 décimales
        } else {
            // Calcul taxe par ligne (plus complexe)
            foreach ($totalsByCustomer[$cid]['items'] as $item) {
                $prod = $products[$item->getProductId()] ?? null;
                if ($prod && ($prod->getTaxable() ?? true) !== false) {
                    $itemTotal = $item['qty'] * ($prod->getPrice() ?? $o->getUnitPrice());
                    $tax += $itemTotal * Config::TAX;
                }
            }
            $tax = round($tax, 2);
        }

        // Frais de port complexes (duplication)
        $ship = 0.0;
        $weight = $totalsByCustomer[$cid]['weight'];

        if ($sub < Config::SHIPPING_LIMIT) {
            $shipZone = $shippingZones[$zone] ?? ['base' => 5.0, 'per_kg' => 0.5];
            $baseShip = $shipZone->getBase();

            if ($weight > 10) {
                $ship = $baseShip + ($weight - 10) * $shipZone->getPerKg();
            } elseif ($weight > 5) {
                // Palier intermédiaire (règle cachée)
                $ship = $baseShip + ($weight - 5) * 0.3;
            } else {
                $ship = $baseShip;
            }

            // Majoration zones éloignées
            if ($zone === 'ZONE3' || $zone === 'ZONE4') {
                $ship = $ship * 1.2;
            }
        } else {
            // Livraison gratuite mais frais manutention poids élevé
            if ($weight > 20) {
                $ship = ($weight - 20) * 0.25;
            }
        }

        // Frais de gestion (magic number + condition cachée)
        $handling = 0.0;
        $itemCount = count($totalsByCustomer[$cid]['items']);
        if ($itemCount > 10) {
            $handling = Config::HANDLING_FEE;
        }
        if ($itemCount > 20) {
            $handling = Config::HANDLING_FEE * 2; // double pour grosses commandes
        }

        // Conversion devise (règle cachée pour non-EUR)
        $currencyRate = 1.0;
        if ($currency === 'USD') {
            $currencyRate = 1.1;
        } elseif ($currency === 'GBP') {
            $currencyRate = 0.85;
        }

        $total = round(($taxable + $tax + $ship + $handling) * $currencyRate, 2);
        $grandTotal += $total;
        $totalTaxCollected += $tax * $currencyRate;

        // Formatage texte (dispersé, pas de fonction dédiée)
        $outputLines[] = sprintf('Customer: %s (%s)', $name, $cid);
        $outputLines[] = sprintf('Level: %s | Zone: %s | Currency: %s', $level, $zone, $currency);
        $outputLines[] = sprintf('Subtotal: %.2f', $sub);
        $outputLines[] = sprintf('Discount: %.2f', $finalDiscount['totalDiscount']);
        $outputLines[] = sprintf('  - Volume discount: %.2f', $finalDiscount['disc']);
        $outputLines[] = sprintf('  - Loyalty discount: %.2f', $finalDiscount['loyaltyDiscount']);
        if ($totalsByCustomer[$cid]['morningBonus'] > 0) {
            $outputLines[] = sprintf('  - Morning bonus: %.2f', $totalsByCustomer[$cid]['morningBonus']);
        }
        $outputLines[] = sprintf('Tax: %.2f', $tax * $currencyRate);
        $outputLines[] = sprintf('Shipping (%s, %.1fkg): %.2f', $zone, $weight, $ship);
        if ($handling > 0) {
            $outputLines[] = sprintf('Handling (%d items): %.2f', $itemCount, $handling);
        }
        $outputLines[] = sprintf('Total: %.2f %s', $total, $currency);
        $outputLines[] = sprintf('Loyalty Points: %d', floor($pts));
        $outputLines[] = '';

        // Export JSON en parallèle (side effect)
        $jsonData[] = [
            'customer_id' => $cid,
            'name' => $name,
            'total' => $total,
            'currency' => $currency,
            'loyalty_points' => floor($pts)
        ];
    }

    $outputLines[] = sprintf('Grand Total: %.2f EUR', $grandTotal);
    $outputLines[] = sprintf('Total Tax Collected: %.2f EUR', $totalTaxCollected);

    $result = implode("\n", $outputLines);

    // Side effects: echo + file write
    echo $result;

    // Export JSON surprise
    $outputPath = $base . '/output.json';
    file_put_contents($outputPath, json_encode($jsonData, JSON_PRETTY_PRINT));

    return $result;
}

// Point d'entrée
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    run();
}
