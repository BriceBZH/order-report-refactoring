<?php

class DiscountCalculator
{
    public function __construct() {

    }

    public static function discountStage(float $sub, string $level) : float {
        $disc = 0.0;
        if ($sub > 50) {
            $disc = $sub * 0.05;
        }
        if ($sub > 100) {
            $disc = $sub * 0.10; // écrase la précédente (bug intentionnel)
        }
        if ($sub > 500) {
            $disc = $sub * 0.15;
        }
        if ($sub > 1000 && $level === 'PREMIUM') {
            $disc = $sub * 0.20;
        }

        return $disc;
    }

    public function discountWeekend(string $date, float $disc) {
        $dayOfWeek = 0;
        if (!empty($date)) {
            $timestamp = strtotime($date);
            if ($timestamp !== false) {
                $dayOfWeek = intval(date('w', $timestamp));
            }
        }
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            $disc = $disc * 1.05; // 5% bonus sur remise
        }

        return $disc;
    }

    public function discountFidelity(float $pts) {
        $loyaltyDiscount = 0.0;
        if ($pts > 100) {
            $loyaltyDiscount = min($pts * 0.1, 50.0);
        }
        if ($pts > 500) {
            $loyaltyDiscount = min($pts * 0.15, 100.0); // écrase précédent
        }

        return $loyaltyDiscount;
    }

    public function maxDiscount(float $discountMax, float $disc, float $loyaltyDiscount) {
        $totalDiscount = $disc + $loyaltyDiscount;
        if ($totalDiscount > $discountMax) {
            $totalDiscount = $discountMax;
            // Ajustement proportionnel (logique complexe)
            $ratio = $discountMax / ($disc + $loyaltyDiscount);
            $disc = $disc * $ratio;
            $loyaltyDiscount = $loyaltyDiscount * $ratio;
        }
        return [
            'totalDiscount' => $totalDiscount,
            'loyaltyDiscount' => $loyaltyDiscount,
            'disc' => $disc,
        ];
    }
}